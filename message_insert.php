<meta charset='utf-8'>
<?php
    session_start();

    if (!isset($_SESSION["userid"])) {
        echo("
            <script>
            alert('로그인 후 이용해 주세요!');
            history.go(-1);
            </script>
        ");
        exit;
    }

    $send_id = $_SESSION["userid"];
    $userlevel = $_SESSION["userlevel"];

    // 관리자면 직접 입력한 수신자, 일반 유저면 무조건 'admin'
    if ($userlevel == 1) {
        $rv_id = $_POST["rv_id"];
    } else {
        $rv_id = "admin";
    }

    $subject = htmlspecialchars($_POST["subject"], ENT_QUOTES);
    $content = htmlspecialchars($_POST["content"], ENT_QUOTES);
    $regist_day = date("Y-m-d (H:i)");

    $con = mysqli_connect("localhost", "user1", "12345", "term_project");

    // 수신자 존재 여부 확인
    $sql = "SELECT * FROM members WHERE id='$rv_id'";
    $result = mysqli_query($con, $sql);
    $num_record = mysqli_num_rows($result);

    if ($num_record) {
        // 메시지 저장
        $sql = "INSERT INTO message (send_id, rv_id, subject, content, regist_day) 
                VALUES ('$send_id', '$rv_id', '$subject', '$content', '$regist_day')";
        mysqli_query($con, $sql);

        echo "
            <script>
                alert('쪽지를 보냈습니다.');
                location.href = 'message_box.php?mode=send';
            </script>
        ";
    } else {
        echo("
            <script>
            alert('수신자 아이디가 존재하지 않습니다!');
            history.go(-1);
            </script>
        ");
    }

    mysqli_close($con);
?>
