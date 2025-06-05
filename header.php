<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userid = isset($_SESSION["userid"]) ? $_SESSION["userid"] : "";
$username = isset($_SESSION["username"]) ? $_SESSION["username"] : "";
$userlevel = $_SESSION['userlevel'] ?? "";
?>

<div id="top">
    <h3>
        <a href="index.php" style="font-size: 32px; font-family: sans-serif;">🎮 게임랜드</a>
    </h3>

    <ul id="top_menu">
        <?php if (!$userid) { ?>
            <li><a href="member_form.php">회원가입</a></li>
            <li> | </li>
            <li><a href="login_form.php">로그인</a></li>
        <?php } else { 
            // 로그인된 상태
            if ($userlevel == 1) { ?>
                <li><a href="admin.php">관리자 모드</a></li>
                <li> | </li>
            <?php } 
            $logged = htmlspecialchars($username) . "(" . htmlspecialchars($userid) . ")님 환영합니다.";
            ?>
            <li><?= $logged ?></li>
            <li> | </li>
            <li><a href="logout.php">로그아웃</a></li>
            <li> | </li>
            <li><a href="member_modify_form.php">정보수정</a></li>
        <?php } ?>
    </ul>
</div>

<nav id="menu_bar">
    <ul>
        <li><a href="index.php">홈</a></li>
        <li><a href="board_list.php">게시판</a></li>
        <li><a href="tetris.php">테트리스</a></li>
        <li><a href="apple_game.php">사과 게임</a></li>
        <li><a href="2048.php">2048 게임</a></li>
        <li><a href="message_form.php">고객센터</a></li>
    </ul>
</nav>
