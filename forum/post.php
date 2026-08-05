<?php
$postID = $_GET['postID'] ?? null;

if (!isset($postID)) {
    header("Location: main.php");
    exit;
}

session_start();

$logID = $_SESSION['loggedID'] ?? $_COOKIE['loggedID'] ?? null;
$logUser = $_SESSION['loggedUser'] ?? $_COOKIE['loggedUser'] ?? null;
$logUserRole = $_SESSION['loggedUserRole'] ?? $_COOKIE['loggedUserRole'] ?? null;

include('../resource/conn.php');

// Post
$stmt1 = $conn->prepare("
    SELECT Post.title, Post.description, Post.postPhoto, Post.postDate, Post.userID, 
           User.username, User.userPhoto, COUNT(Upvote.contentNo) AS upvotes, 
           MAX(CASE WHEN Upvote.userID = :logID THEN 1 ELSE 0 END) AS hasUpvoted, 
           MAX(CASE WHEN Bookmark.userID = :logID THEN 1 ELSE 0 END) as hasBookmarked 
    FROM Post
    LEFT JOIN User ON Post.userID = User.userID
    LEFT JOIN Upvote ON Upvote.contentNo = Post.postID AND Upvote.contentType = 'Post'
    LEFT JOIN Bookmark ON Bookmark.postID = Post.postID
    WHERE Post.postID = :postID
    GROUP BY Post.postID
");
$stmt1->bindParam(':postID', $postID, PDO::PARAM_INT);
$stmt1->bindParam(':logID', $logID, PDO::PARAM_INT);
$stmt1->execute();
$post = $stmt1->fetch(PDO::FETCH_ASSOC);

// Comments
$stmt2 = $conn->prepare("
    SELECT Comment.commentID, Comment.description, Comment.commPhoto, Comment.commentDate,
           Comment.userID, User.username, User.userPhoto, COUNT(Upvote.contentNo) AS upvotes, 
           MAX(CASE WHEN Upvote.userID = :logID THEN 1 ELSE 0 END) AS hasUpvoted 
    FROM Comment
    LEFT JOIN User ON Comment.userID = User.userID
    LEFT JOIN Upvote ON Upvote.contentNo = Comment.commentID AND Upvote.contentType = 'Comment'
    WHERE postID = :postID
    GROUP BY Comment.commentID
");
$stmt2->bindParam(':postID', $postID, PDO::PARAM_INT);
$stmt2->bindParam(':logID', $logID, PDO::PARAM_INT);
$stmt2->execute();
$comments = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// For summary
$summaryPost = <<<TEXT
Title: {$post['title']}
Posted by: {$post['username']} on {$post['postDate']}

{$post['description']}
TEXT;

$summaryComment = "";
foreach ($comments as $c) {
    $summaryComment .= "- {$c['username']} ({$c['commentDate']}): {$c['description']}\n";
}

// Extract links from prepared summary data
preg_match_all('/https?:\/\/\S+/', $post['description'], $matches);
$articleLinks = $matches[0] ?? [];

// Helper function to fetch and clean external article content
function fetch_and_clean_article($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    curl_setopt($ch, CURLOPT_REFERER, "https://www.google.com/");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.9',
        'Connection: keep-alive',
    ]);

    $html = curl_exec($ch);
    curl_close($ch);

    if (!$html)
        return null;

    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    libxml_clear_errors();

    $tagsToTry = ['article', 'main', 'body'];
    $textContent = '';

    foreach ($tagsToTry as $tag) {
        $elements = $doc->getElementsByTagName($tag);
        if ($elements->length > 0) {
            $textContent = $elements->item(0)->textContent;
            break;
        }
    }

    $textContent = preg_replace('/\s+/', ' ', $textContent);
    return trim($textContent);
}

// Fetch content from linked articles (if any)
$externalContent = "";
foreach ($articleLinks as $link) {
    $articleText = fetch_and_clean_article($link);
    if ($articleText) {
        $externalContent .= "\n\nReferenced Article Content from $link:\n" . $articleText;
    }
}

// AI module
require '../vendor/autoload.php';

use OpenAI\Client;

// Handle AJAX summarization request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['ajax']) && $_GET['ajax'] === 'summary') {

    $client = OpenAI::client('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'); // Replace with your real key

    $input = json_decode(file_get_contents("php://input"), true);
    $mode = $input['mode'] ?? 'brief';
    $prompt = match ($mode) {
        'abstract' => "Summarize these abstractively (in general concept):",
        'extract' => "Summarize these extractively (extract main point):",
        'general' => "Summarize these in general : ",
        default => "Summarize:"
    };

    $input = "$prompt\n$summaryPost\n$summaryComment$externalContent";

    $response = $client->chat()->create([
        'model' => 'gpt-4o',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful summary assistant for WeDoCare forum about medical topics. '
                . 'Assume the user are medical patients, provide them the necessary information summary based on the input.'
                . 'Format your answers using HTML (e.g., <b>, <i>, <ul>, <li>, <p>) where appropriate. '
                . 'Return "Summary are not available at the moment." if the topics are unrelated to medical topics,'
                . 'or not enough content to generate a helpful summary with answers.'
                . 'Perform cross reference between online source and the discussion to validate the information.'
                . 'If any web links is provided, include them at the end of the summary as a reference.'
                . 'Encourage users to read the web link if summarize on the link cannot be done.'
                . 'Notify users if inconsistency is spotted.'],
            ['role' => 'user', 'content' => $input]
        ],
        'temperature' => 0.5,
        'max_tokens' => 1000,
    ]);

    echo $response['choices'][0]['message']['content'];
    exit;
}
?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>Title : <?= htmlspecialchars($post['title']) ?></title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <?php include("../resource/BootstrapLibrary.php"); ?>
        <link rel='stylesheet' href="../resource/style.css">
    </head>

    <body style="margin-bottom: 200px;">
        <header>
            <h1 class="text-white p-3"><a href="../index.php" class="text-white">WeDoCare</a></h1>
            <nav id="navigator" class="navbar text-white bg-dark">
                <ul><a href="../index.php">Home</a></ul>
                <ul><a href="./main.php">Forum</a></ul>
                <ul><a href="./bookmark.php">Bookmark</a></ul>
                <?php if ($logID && $logUser && $logUserRole == 'User'): ?>
                    <ul><a href="../feedback/create_feedback.php">Feedback</a></ul>
                <?php endif; ?>
                <?php if ($logID && $logUser && $logUserRole == 'Admin'): ?>
                    <ul><a href="../feedback/main.php">Feedbacks</a></ul>
                    <ul><a href="../report/main.php">Reports</a></ul>
                <?php endif; ?>
                <ul><a href="../about.php">About Us</a></ul>
                <ul style="padding-left: 60%;">
                    <?php if ($logID && $logUser): ?>
                        Welcome, <a href="../user/profile.php?userID=<?= $logID ?>"><?= htmlspecialchars($logUser) ?></a>
                    <?php else: ?>
                        <a href="../user/login.php" class="float-right">Login</a>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>

        <?php if (isset($logID)): ?>
            <div id="summary-box" class="container border">
                <h3>AI Summaries</h3>
                <p>Confused about the discussions? Ask an AI assistant for help here.</p>
                <label for="summaryMode">Choose summary type: </label>
                <select id="summaryMode">
                    <option value="general" checked>General</option>
                    <option value="abstract">Abstractive</option>
                    <option value="extract">Extractive</option>
                </select>
                <button class="float-right btn btn-primary" onclick="generateSummary()">Request</button>
                <div id="summary-result"></div>
            </div>
        <?php endif; ?>

        <div class="container">
            <?php if ($post): ?>
                <h1><?= htmlspecialchars($post['title']) ?></h1>
                <div class="bookmark-container float-right">
                    <button class="bookmark-button btn btn-primary" data-postid="<?= $postID ?>">
                        <i class="<?= $post['hasBookmarked'] ? 'fa-solid' : 'fa-regular' ?> fa-bookmark"></i>
                    </button>
                    Bookmark
                </div>
                <div>
                    <img src="../resource/img/user/<?= htmlspecialchars($post['userPhoto']) ?>"
                         onerror="this.onerror=null; this.src='../resource/img/user/placeholder.png'"
                         class="img-thumbnail"
                         style="width: 50px; height: 50px;">
                    <p> Posted by
                        <?php if (isset($post['username'])): ?>
                            <a href="../user/profile.php?userID=<?= $post['userID'] ?>">
                                <?= htmlspecialchars($post['username']) ?>
                            </a>
                        <?php else: ?>
                            [deleted]
                        <?php endif; ?>
                    </p>
                    <p><?= htmlspecialchars($post['postDate']) ?></p>
                    <p><?= nl2br(htmlspecialchars($post['description'])) ?></p>
                    <?php if (!empty($post['postPhoto'])): ?>
                        <a href="../resource/img/post/<?= htmlspecialchars($post['postPhoto']) ?>"><img src="../resource/img/post/<?= htmlspecialchars($post['postPhoto']) ?>" width="500" alt="<?= htmlspecialchars($post['postPhoto']) ?>"></a><br>
                    <?php endif; ?><br>

                    <div>
                        <?php if ($logID == $post['userID']): ?>
                            <form action="function/del_cont_func.php" method="post" class="d-inline">
                                <input type="hidden" name="postID" value="<?= $postID ?>">
                                <input type='hidden' name='photo' value='<?= htmlspecialchars($post['postPhoto']) ?>'>
                                <input type="submit" class="float-right btn btn-secondary" value="Delete">
                            </form>
                        <?php else : ?>
                            <form action="report.php" method="post" class="d-inline">
                                <input type="hidden" name="postID" value="<?= $postID ?>">
                                <input type="submit" class="float-right btn btn-secondary" value="Report">
                            </form>
                        <?php endif; ?>
                        <div class="upvote-container">
                            <button class="upvote-button btn btn-primary" data-postid="<?= $postID ?>">
                                <i class="<?= $post['hasUpvoted'] ? 'fa-solid' : 'fa-regular' ?> fa-thumbs-up"></i>
                            </button>
                            Vote(<span class="upvote-count"><?= $post['upvotes'] ?></span>)
                        </div>
                    </div>
                </div>
                <h3>Comments:</h3>
                <?php if (empty($comments)): ?>
                    <p>No one has responded yet. </p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="container border mb-3 p-3">
                            <img src="../resource/img/user/<?= htmlspecialchars($comment['userPhoto']) ?>"
                                 onerror="this.onerror=null; this.src='../resource/img/user/placeholder.png'"
                                 class="img-thumbnail rounded-circle float-sm-left mr-3"
                                 style="width: 50px; height: 50px;">
                            <p>Posted by
                                <?php if (!empty($comment['username'])): ?>
                                    <a href="../user/profile.php?userID=<?= $comment['userID'] ?>">
                                        <?= htmlspecialchars($comment['username']) ?>
                                    </a>
                                <?php else : ?>
                                    [deleted]
                                <?php endif; ?>
                            </p>
                            <p>on <?= htmlspecialchars($comment['commentDate']) ?></p>
                            <p><?= nl2br(htmlspecialchars($comment['description'])) ?></p>
                            <?php if (!empty($comment['commPhoto'])): ?>
                                <a href=../resource/img/comment/<?= htmlspecialchars($comment['commPhoto']) ?>" /><img src="../resource/img/comment/<?= htmlspecialchars($comment['commPhoto']) ?>" width="500" alt="<?= htmlspecialchars($comment['commPhoto']) ?>"></a><br>
                            <?php endif; ?><br>

                            <div>
                                <?php if ($logID == $comment['userID']): ?>
                                    <form action="function/del_cont_func.php" method="post" class="d-inline">
                                        <input type="hidden" name="commID" value="<?= $comment['commentID'] ?>">
                                        <input type="hidden" name="origin" value="<?= $postID ?>">
                                        <input type='hidden' name='photo' value='<?= htmlspecialchars($comment['commPhoto']) ?>'>
                                        <input type="submit" class="float-right btn btn-secondary" value="Delete">
                                    </form>
                                <?php else: ?>
                                    <form action="report.php" method="post" class="d-inline">
                                        <input type="hidden" name="commentID" value="<?= $comment['commentID'] ?>">
                                        <input type="submit" class="float-right btn btn-secondary" value="Report">
                                    </form>
                                <?php endif; ?>
                                <div class="upvote-container">
                                    <button class="upvote-button btn btn-primary" data-commentid="<?= $comment['commentID'] ?>">
                                        <i class="<?= $comment['hasUpvoted'] ? 'fa-solid' : 'fa-regular' ?> fa-thumbs-up"></i>
                                    </button>
                                    Vote(<span class="upvote-count"><?= $comment['upvotes'] ?></span>)
                                </div>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php else: ?>
                <p>Post not found.</p>
            <?php endif; ?>
        </div>
        <?php if (isset($logID)) : ?>
            <div style="position: fixed; bottom: 0; width: 100%; background: #f9f9f9; padding: 10px; box-shadow: 0 -2px 10px rgba(0,0,0,0.1);">
                <?php if (time() - strtotime($post['postDate']) < 30 * 24 * 60 * 60) : ?>
                    <form action="function/reply_func.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="postID" value="<?= $postID ?>">
                        <textarea maxlength="1000" id="comment" name="comment" placeholder="Write a comment..." rows="5" style="width: 100%; resize: none;" required></textarea><br>
                        <input type="file" name="img" accept="image/png, image/jpeg, image/gif">
                        <input type="submit" class="float-right" value="Submit">
                    </form>
                <?php else : ?>
                    <p>This post was locked as it has already exceed 30 days.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </body>

</html>

<script>
    function generateSummary() {
        const mode = document.getElementById('summaryMode').value;
        const resultDiv = document.getElementById('summary-result');
        resultDiv.innerHTML = '<p>Generating summary...</p>';

        fetch('post.php?postID=<?= $postID ?>&ajax=summary', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'summarize',
                mode: mode
            })
        })
                .then(response => response.text())
                .then(data => {
                    document.getElementById('summary-result').innerHTML = data;
                })
                .catch(err => {
                    resultDiv.innerHTML = '<p class="text-danger">Failed to get summary.</p>';
                    console.error(err);
                });
    }
    document.addEventListener('DOMContentLoaded', function () {
        // --- Upvote Logic ---
        document.querySelectorAll('.upvote-button').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();

                const container = this.closest('.upvote-container');
                const postID = this.dataset.postid;
                const commentID = this.dataset.commentid;

                const formData = new URLSearchParams();
                if (postID) {
                    formData.append('postID', postID);
                } else if (commentID) {
                    formData.append('commentID', commentID);
                }

                fetch('function/upvote_func.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formData.toString()
                })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                container.querySelector('.upvote-count').textContent = data.upvotes;
                                if (data.action === 'upvoted') {
                                    button.innerHTML = '<i class="fa-solid fa-thumbs-up"></i>';
                                } else if (data.action === 'unvoted') {
                                    button.innerHTML = '<i class="fa-regular fa-thumbs-up"></i>';
                                }

                            } else {
                                alert("Error processing upvote.");
                            }
                        })
                        .catch(err => console.error(err));
            });
        });

        // --- Bookmark Logic ---
        document.querySelectorAll('.bookmark-button').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();

                const container = this.closest('.bookmark-container');
                const postID = this.dataset.postid;

                const formData = new URLSearchParams();
                if (postID) {
                    formData.append('postID', postID);
                }

                fetch('function/bookmark_func.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formData.toString()
                })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                if (data.action === 'added') {
                                    button.innerHTML = '<i class="fa-solid fa-bookmark"></i>';
                                } else if (data.action === 'removed') {
                                    button.innerHTML = '<i class="fa-regular fa-bookmark"></i>';
                                }

                            } else {
                                alert("Error processing bookmark.");
                            }
                        })
                        .catch(err => console.error(err));
            });
        });
        // --- Deletion Confirmation ---
        document.querySelectorAll('form[action="function/del_cont_func.php"]').forEach(form => {
            form.addEventListener('submit', function (e) {
                const isPost = form.querySelector('input[name="postID"]') !== null;
                const isComment = form.querySelector('input[name="commID"]') !== null;

                const message = isPost ?
                        "Are you sure you want to delete this post? This action cannot be undone." :
                        "Are you sure you want to delete this comment?";

                if (!confirm(message)) {
                    e.preventDefault(); // Cancel the form submission
                }
            });
        });
    });
</script>
