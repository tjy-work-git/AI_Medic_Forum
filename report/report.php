<?php
$reportID = $_GET['reportID'] ?? null;

if (!isset($reportID)) {
	header("Location: main.php");
	exit;
}

session_start();

$logID = $_SESSION['loggedID'] ?? $_COOKIE['loggedID'] ?? null;
$logUser = $_SESSION['loggedUser'] ?? $_COOKIE['loggedUser'] ?? null;
$logUserRole = $_SESSION['loggedUserRole'] ?? $_COOKIE['loggedUserRole'] ?? null;

include('../resource/conn.php');

// Report
$stmt1 = $conn->prepare("
        SELECT Report.reportID, Report.reportDesc, Report.reportNo, Report.reportDate, Report.status, Post.title AS postTitle, User.username
        FROM Report
        LEFT JOIN User ON Report.userID = User.userID
        LEFT JOIN Post ON Report.reportNo = Post.postID
    WHERE Report.reportID = :reportID
    GROUP BY Report.reportID
");
$stmt1->bindParam(':reportID', $reportID, PDO::PARAM_INT);
$stmt1->execute();
$report = $stmt1->fetch(PDO::FETCH_ASSOC);

// Post
$stmtPost = $conn->prepare("
    SELECT Post.postID, Post.title, Post.description, Post.postPhoto, Post.postDate, Post.userID, 
           User.username, User.userPhoto 
    FROM Post
    LEFT JOIN User ON Post.userID = User.userID
    WHERE Post.postID = :postID
    GROUP BY Post.postID
");
$stmtPost->bindParam(':postID', $report['reportNo'], PDO::PARAM_INT);
$stmtPost->execute();
$post = $stmtPost->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<title>Report on <?= htmlspecialchars($report['postTitle']) ?></title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<?php include("../resource/BootstrapLibrary.php"); ?>
	<link rel='stylesheet' href="../resource/style.css">
</head>

<body style="margin-bottom: 200px;">
	<header>
		<h1 class="text-white p-3"><a href="../index.php" class="text-white">WeDoCare</a></h1>
		<nav id="navigator" class="navbar text-white bg-dark">
			<ul><a href="../index.php">Home</a></ul>
			<ul><a href="../forum/main.php">Forum</a></ul>
			<ul><a href="../forum/bookmark.php">Bookmark</a></ul>
			<?php if ($logID && $logUser && $logUserRole == 'Admin'): ?>
				<ul><a href="../feedback/main.php">Feedbacks</a></ul>
				<ul><a href="./main.php">Reports</a></ul>
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

	<div class="container">
		<?php if ($report): ?>
			<?php
			$color = 'btn-primary';
			switch ($report['status']) {
				case 'Pending':
					$color = 'badge-warning';
					break;
				case 'Completed':
					$color = 'badge-success';
					break;
				case 'Rejected':
					$color = 'badge-danger';
					break;
			}
			?>
			<div class="badge <?= $color ?>"><?= $report['status'] ?></div>
			<h1>Report on <?= htmlspecialchars($report['postTitle']) ?></h1>
			<div class="float-right">
				<?php if ($report['status'] == 'Pending'): ?>
					<button id='reject-btn' class="btn btn-warning" data-toggle="modal" data-target="#rejectedModal" data-reportid="<?= $reportID ?>">
						Mark as Rejected
					</button>
					<button id='delete-btn' class="btn btn-danger" data-toggle="modal" data-target="#deleteAndBanModal" data-postid="<?= $report['reportNo'] ?>" data-reportid="<?= $reportID ?>">
						Delete and Ban
					</button>
				<?php endif; ?>
			</div>
			<div>
				<img src="../resource/img/user/<?= htmlspecialchars($report['userPhoto']) ?>"
					onerror="this.onerror=null; this.src='../resource/img/user/placeholder.png'"
					class="img-thumbnail"
					style="width: 50px; height: 50px;">
				<p> Reported by
					<?php if (isset($report['username'])): ?>
						<a href="../user/profile.php?userID=<?= $report['userID'] ?>">
							<?= htmlspecialchars($report['username']) ?>
						</a>
					<?php else: ?>
						[deleted]
					<?php endif; ?>
				</p>
				<p><?= htmlspecialchars($report['reportDate']) ?></p>
				<p><?= nl2br(htmlspecialchars($report['reportDesc'])) ?></p>
			</div>
			<div class="container" id="post-snippet">
				<?php if ($post): ?>
					<h5 class="text-primary" id='container-title'>Post Snippet </h5>
					<h1><a href='../forum/post.php?postID=<?= $post['postID'] ?>'><?= $post['title'] ?></a></h1>
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
					</div>

				<?php else: ?>
					<h5 class="text-danger" id='container-title'>Post Deleted</h5>
				<?php endif; ?>
			</div>
		<?php else: ?>
			<p>Report not found.</p>
		<?php endif; ?>
	</div>

	<!--Delete and Ban Modal -->
	<div class="modal fade" id="deleteAndBanModal" tabindex="-1" role="dialog" aria-labelledby="deleteAndBanModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="deleteAndBanModalLabel">Confirm Delete and Ban</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					Are you sure you want to remove this post and ban this user?
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary" id="confirm-delete">Confirm</button>
				</div>
			</div>
		</div>
	</div>

	<!--Rejected Modal -->
	<div class="modal fade" id="rejectedModal" tabindex="-1" role="dialog" aria-labelledby="rejectedModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="rejectedModalLabel">Confirm Reject</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					Are you sure you want to reject this report?
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary" id='confirm-reject'>Confirm</button>
				</div>
			</div>
		</div>
	</div>

	<!--Reject Success Modal -->
	<div class="modal fade" id="rejectedSuccessModal" tabindex="-1" role="dialog" aria-labelledby="rejectedSuccessModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="rejectedSuccessModalLabel">Rejection Success</h5>
				</div>
				<div class="modal-body">
					Report rejected.
				</div>
				<div class="modal-footer">
				</div>
			</div>
		</div>
	</div>

	<!--Delete Success Modal -->
	<div class="modal fade" id="deleteSuccessModal" tabindex="-1" role="dialog" aria-labelledby="deleteSuccessModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="deleteSuccessModalLabel">
						Removal and Banning Success
					</h5>
				</div>
				<div class="modal-body">
					The reported post has been deleted and the user has been ban.
				</div>
				<div class="modal-footer">
				</div>
			</div>
		</div>
	</div>
</body>

</html>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		// on reject confirm
		const deleteBtn = $('#delete-btn')
		const rejectBtn = $('#reject-btn')
		const confirmReject = $('#confirm-reject')
		const confirmDelete = $('#confirm-delete')
		const rejectedSuccessModal = $('#rejectedSuccessModal')

		if (rejectBtn) {
			rejectBtn.on('click', function() {
				confirmReject.data('reportid', rejectBtn.data('reportid'))
			})
		}

		if (deleteBtn) {
			deleteBtn.on('click', function() {
				confirmDelete.data('postid', deleteBtn.data('postid'))
				confirmDelete.data('reportid', deleteBtn.data('reportid'))
			})
		}

		confirmReject.on('click', function(e) {
			e.preventDefault();

			const reportID = confirmReject.data('reportid')

			const formData = new URLSearchParams();
			if (reportID) {
				formData.append('reportID', reportID);
			}

			fetch('function/reject_func.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					},
					body: formData.toString()
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						$('#rejectedModal').modal('hide')
						$('#rejectedSuccessModal').modal('show')
						setTimeout(function() {
							window.location.reload()
						}, 1000)
					} else {
						alert("Error rejecting report.");
					}
				})
				.catch(err => console.error(err));
		});

		confirmDelete.on('click', function(e) {
			e.preventDefault();

			const postID = confirmDelete.data('postid');
			const reportID = confirmDelete.data('reportid');

			const formData = new URLSearchParams();
			if (postID) {
				formData.append('postID', postID);
				formData.append('reportID', reportID);
			}

			fetch('function/delete_ban_func.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					},
					body: formData.toString()
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						$('#deleteAndBanModal').modal('hide')
						$('#deleteSuccessModal').modal('show')
						setTimeout(function() {
							window.location.reload()
						}, 1000)
					} else {
						alert("Error deleting post and banning user.");
					}
				})
				.catch(err => console.error(err));
		});

	})
</script>
