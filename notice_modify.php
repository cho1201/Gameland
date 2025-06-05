<?php
    $num = $_GET["num"];
    $page = $_GET["page"];

    $subject = $_POST["subject"];
    $content = $_POST["content"];
          
    $con = mysqli_connect("localhost", "user1", "12345", "term_project");
    $sql = "UPDATE notice SET subject='$subject', content='$content' WHERE num=$num";
    mysqli_query($con, $sql);

    mysqli_close($con);     

    echo "
	      <script>
	          location.href = 'notice_list.php?page=$page';
	      </script>
	  ";
?>
