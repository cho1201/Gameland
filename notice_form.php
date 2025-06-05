<?php
session_start();
if (!isset($_SESSION["userid"]) || $_SESSION["userlevel"] != 1) {
    echo "<script>
            alert('관리자만 접근 가능합니다.');
            history.back();
          </script>";
    exit;
}

$username = $_SESSION["username"];
?>
<!DOCTYPE html>
<html>
<head> 
<meta charset="utf-8">
<title>공지사항 - 게임랜드</title>
<link rel="stylesheet" type="text/css" href="./css/common.css">
<link rel="stylesheet" type="text/css" href="./css/board.css">
<script>
  function check_input() {
      if (!document.board_form.subject.value)
      {
          alert("제목을 입력하세요!");
          document.board_form.subject.focus();
          return;
      }
      if (!document.board_form.content.value)
      {
          alert("내용을 입력하세요!");    
          document.board_form.content.focus();
          return;
      }
      document.board_form.submit();
   }
</script>
</head>
<body> 
<header>
    <?php include "header.php";?>
</header>  
<section>
	<div id="main_img_bar">
        <img src="img/main_img.png">
    </div>
   	<div id="board_box">
	    <h3 id="board_title">
	    		공지사항 > 글 쓰기
		</h3>
	    <form  name="board_form" method="post" action="notice_insert.php" enctype="multipart/form-data">
	    	 <ul id="board_form">
				<li>
					<span class="col1">이름 : </span>
					<span class="col2"><?= htmlspecialchars($username) ?></span>
				</li>		
	    		<li>
	    			<span class="col1">제목 : </span>
	    			<span class="col2"><input name="subject" type="text"></span>
	    		</li>	    	
	    		<li id="text_area">	
	    			<span class="col1">내용 : </span>
	    			<span class="col2">
	    				<textarea name="content"></textarea>
	    			</span>
	    		</li>
	    		<li>
			        <span class="col1"> 첨부 파일</span>
			        <span class="col2"><input type="file" name="upfile"></span>
			    </li>
	    	    </ul>
	    	<ul class="buttons">
				<li><button type="button" onclick="check_input()">완료</button></li>
				<li><button type="button" onclick="location.href='notice_list.php'">목록</button></li>
			</ul>
	    </form>
	</div>
</section> 
<footer>
    <?php include "footer.php";?>
</footer>
</body>
</html>
