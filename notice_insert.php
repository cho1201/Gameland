<meta charset="utf-8">
<?php
session_start();

$userid = $_SESSION["userid"] ?? "";
$username = $_SESSION["username"] ?? "";
$userlevel = $_SESSION["userlevel"] ?? "";

if (!$userid || $userlevel != 1) {
    echo "<script>
            alert('공지사항 등록은 관리자만 가능합니다!');
            history.go(-1);
          </script>";
    exit;
}

$subject = htmlspecialchars($_POST["subject"], ENT_QUOTES);
$content = htmlspecialchars($_POST["content"], ENT_QUOTES);
$regist_day = date("Y-m-d (H:i)");

$upload_dir = './data/';
$upfile_name = $_FILES["upfile"]["name"];
$upfile_tmp_name = $_FILES["upfile"]["tmp_name"];
$upfile_type = $_FILES["upfile"]["type"];
$upfile_size = $_FILES["upfile"]["size"];
$upfile_error = $_FILES["upfile"]["error"];

$copied_file_name = "";
if ($upfile_name && !$upfile_error) {
    $file_info = pathinfo($upfile_name);
    $file_ext = strtolower($file_info["extension"] ?? "");

    $blocked_exts = ['php', 'exe', 'js', 'sh'];
    if (in_array($file_ext, $blocked_exts)) {
        echo "<script>
                alert('이 확장자($file_ext)의 파일은 업로드할 수 없습니다.');
                history.go(-1);
              </script>";
        exit;
    }

    if ($upfile_size > 1000000) {
        echo "<script>
                alert('업로드 파일 크기가 1MB를 초과합니다.\\n파일 크기를 확인해주세요.');
                history.go(-1);
              </script>";
        exit;
    }

    $new_file_name = date("Y_m_d_H_i_s") . "_" . rand(1000, 9999);
    $copied_file_name = $new_file_name . "." . $file_ext;
    $uploaded_file = $upload_dir . $copied_file_name;

    if (!move_uploaded_file($upfile_tmp_name, $uploaded_file)) {
        echo "<script>
                alert('파일 업로드에 실패했습니다.');
                history.go(-1);
              </script>";
        exit;
    }
}

$con = mysqli_connect("localhost", "user1", "12345", "term_project");

$subject = mysqli_real_escape_string($con, $subject);
$content = mysqli_real_escape_string($con, $content);

$sql = "INSERT INTO notice (id, name, subject, content, regist_day, hit, file_name, file_type, file_copied)
        VALUES ('$userid', '$username', '$subject', '$content', '$regist_day', 0,
                '$upfile_name', '$upfile_type', '$copied_file_name')";
mysqli_query($con, $sql);

mysqli_close($con);

echo "<script>
        location.href = 'notice_list.php';
      </script>";
?>
