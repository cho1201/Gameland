<?php
    $num   = $_GET["num"];
    $page  = $_GET["page"];

    $con = mysqli_connect("localhost", "user1", "12345", "term_project");
    $sql = "select * from notice where num = $num";
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_array($result);

    $copied_name = $row["file_copied"];

    if ($copied_name)
    {
        $file_path = "./data/" . $copied_name;
        if (file_exists($file_path)) {
            unlink($file_path);  // 파일 삭제
        }
    }

    $sql = "delete from notice where num = $num";
    mysqli_query($con, $sql);
    mysqli_close($con);

    echo "
         <script>
             location.href = 'notice_list.php?page=$page';
         </script>
       ";
?>
