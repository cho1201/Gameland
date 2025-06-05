<?php
    $id   = $_POST["id"];
    $pass = $_POST["pass"];
    $name = $_POST["name"];
    $email1  = $_POST["email1"];
    $email2  = $_POST["email2"];

    $email = $email1 . "@" . $email2;
    $regist_day = date("Y-m-d (H:i)");

    $con = mysqli_connect("localhost", "user1", "12345", "term_project");

    // 1. 회원 정보 저장
    $sql = "INSERT INTO members(id, pass, name, email, regist_day, level, point) 
            VALUES('$id', '$pass', '$name', '$email', '$regist_day', 9, 0)";
    $result = mysqli_query($con, $sql);

    if ($result) {
        $user_num = mysqli_insert_id($con);

        // user_scores 테이블에 3개 게임 초기화
        $score_sql = "
            INSERT INTO user_scores (num, game_name, max_score)
            VALUES 
                ($user_num, 'apple', 0),
                ($user_num, 'tetris', 0),
                ($user_num, '2048', 0)
        ";
        $score_result = mysqli_query($con, $score_sql);

        if (!$score_result) {
            echo "<script>alert('회원 점수 정보 저장 실패: " . mysqli_error($con) . "');</script>";
        }
    }

    mysqli_close($con);

    echo "
          <script>
              location.href = 'index.php';
          </script>
      ";
?>
