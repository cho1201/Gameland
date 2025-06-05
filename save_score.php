<?php
session_start();
header('Content-Type: application/json');

// 로그인 여부 확인
if (!isset($_SESSION["userid"])) {
    echo json_encode(["success" => false, "message" => "로그인이 필요합니다."]);
    exit;
}

$userid = $_SESSION["userid"];
$score = isset($_POST["score"]) ? floatval($_POST["score"]) : 0;
$game_name = isset($_POST["game_name"]) ? $_POST["game_name"] : '';

// 허용된 게임 목록
$allowed_games = ['apple', 'tetris', 'even'];

// 유효성 검사
if (!in_array($game_name, $allowed_games)) {
    echo json_encode(["success" => false, "message" => "잘못된 게임 이름입니다."]);
    exit;
}

if ($score <= 0) {
    echo json_encode(["success" => false, "message" => "0 이하의 점수는 저장되지 않습니다."]);
    exit;
}

// DB 연결
$con = mysqli_connect("localhost", "user1", "12345", "term_project");
if (!$con) {
    echo json_encode(["success" => false, "message" => "DB 연결 실패"]);
    exit;
}

// SQL 인젝션 대비
$userid_esc = mysqli_real_escape_string($con, $userid);
$game_name_esc = mysqli_real_escape_string($con, $game_name);

// 사용자 num 가져오기
$sql = "SELECT num FROM members WHERE id = '$userid_esc'";
$result = mysqli_query($con, $sql);
if (!$result) {
    mysqli_close($con);
    echo json_encode(["success" => false, "message" => "회원 정보 조회 실패"]);
    exit;
}

if ($row = mysqli_fetch_assoc($result)) {
    $user_num = (int)$row["num"];

    // 기존 점수 확인
    $check_sql = "SELECT max_score FROM user_scores WHERE num = $user_num AND game_name = '$game_name_esc'";
    $check_result = mysqli_query($con, $check_sql);

    if ($check_result && ($row2 = mysqli_fetch_assoc($check_result))) {
        $current_max = (float)$row2["max_score"];

        // 최고 점수 갱신
        if ($score > $current_max) {
            $update_sql = "UPDATE user_scores SET max_score = $score WHERE num = $user_num AND game_name = '$game_name_esc'";
            if (mysqli_query($con, $update_sql)) {
                echo json_encode(["success" => true, "message" => "🎉 최고 점수 갱신!"]);
            } else {
                echo json_encode(["success" => false, "message" => "점수 갱신 실패"]);
            }
        } else {
            echo json_encode(["success" => true, "message" => "기존 최고 점수보다 낮습니다."]);
        }
    } else {
        // 점수 기록이 없으면 새로 추가
        $insert_sql = "INSERT INTO user_scores (num, game_name, max_score) VALUES ($user_num, '$game_name_esc', $score)";
        if (mysqli_query($con, $insert_sql)) {
            echo json_encode(["success" => true, "message" => "최초 점수 저장 완료!"]);
        } else {
            echo json_encode(["success" => false, "message" => "점수 저장 실패"]);
        }
    }
} else {
    echo json_encode(["success" => false, "message" => "사용자 정보를 찾을 수 없습니다."]);
}

mysqli_close($con);
exit;
?>
