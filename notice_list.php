<!DOCTYPE html>
<html>
<head> 
<meta charset="utf-8">
<title>공지사항 - 게임랜드</title>
<link rel="stylesheet" type="text/css" href="./css/common.css">
<link rel="stylesheet" type="text/css" href="./css/board.css">
</head>
<body> 
<header>
    <?php include "header.php"; ?>
</header>  
<section>
	<div id="main_img_bar">
        <img src="./img/main_img.png">
    </div>
   	<div id="board_box">
	    <h3>
	    	공지사항 > 목록보기
		</h3>
	    <ul id="board_list">
			<li>
				<span class="col1">번호</span>
				<span class="col2">제목</span>
				<span class="col3">작성자</span>
				<span class="col4">첨부</span>
				<span class="col5">등록일</span>
				<span class="col6">조회</span>
			</li>
<?php
	if (session_status() === PHP_SESSION_NONE) {
	    session_start();
	}

	$userid = $_SESSION["userid"] ?? "";
	$userlevel = $_SESSION["userlevel"] ?? "";

	$page = $_GET["page"] ?? 1;

	$con = mysqli_connect("localhost", "user1", "12345", "term_project");
	$sql = "SELECT * FROM notice ORDER BY num DESC";
	$result = mysqli_query($con, $sql);
	$total_record = mysqli_num_rows($result);

	$scale = 10;
	$total_page = ($total_record % $scale == 0) ? ($total_record / $scale) : (floor($total_record / $scale) + 1);
	$start = ($page - 1) * $scale;
	$number = $total_record - $start;

	for ($i = $start; $i < $start + $scale && $i < $total_record; $i++) {
		mysqli_data_seek($result, $i);
		$row = mysqli_fetch_array($result);

		$num = $row["num"];
		$name = $row["name"];
		$subject = $row["subject"];
		$regist_day = $row["regist_day"];
		$hit = $row["hit"];
		$file_image = $row["file_name"] ? "<img src='./img/file.gif'>" : " ";
?>
			<li>
				<span class="col1"><?= $number ?></span>
				<span class="col2"><a href="notice_view.php?num=<?= $num ?>&page=<?= $page ?>"><?= $subject ?></a></span>
				<span class="col3"><?= $name ?></span>
				<span class="col4"><?= $file_image ?></span>
				<span class="col5"><?= $regist_day ?></span>
				<span class="col6"><?= $hit ?></span>
			</li>
<?php
		$number--;
	}
	mysqli_close($con);
?>
	    </ul>
		<ul id="page_num"> 	
<?php
	if ($total_page >= 2 && $page >= 2) {
		$new_page = $page - 1;
		echo "<li><a href='notice_list.php?page=$new_page'>◀ 이전</a></li>";
	} else {
		echo "<li>&nbsp;</li>";
	}

	for ($i = 1; $i <= $total_page; $i++) {
		if ($page == $i)
			echo "<li><b> $i </b></li>";
		else
			echo "<li><a href='notice_list.php?page=$i'> $i </a></li>";
	}

	if ($total_page >= 2 && $page != $total_page) {
		$new_page = $page + 1;
		echo "<li><a href='notice_list.php?page=$new_page'>다음 ▶</a></li>";
	} else {
		echo "<li>&nbsp;</li>";
	}
?>
		</ul> <!-- page -->	    	
		<ul class="buttons">
			<li><button onclick="location.href='notice_list.php'">목록</button></li>
			<li>
<?php 
	if ($userid && $userlevel == 1) { // 관리자만 공지 작성
?>
				<button onclick="location.href='notice_form.php'">글쓰기</button>
<?php
	} else {
?>
				<a href="javascript:alert('관리자만 작성할 수 있습니다.')"><button>글쓰기</button></a>
<?php
	}
?>
			</li>
		</ul>
	</div> <!-- board_box -->
</section> 
<footer>
    <?php include "footer.php"; ?>
</footer>
</body>
</html>
