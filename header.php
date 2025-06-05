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
        <a href="index.html" style="font-size: 32px; font-family: sans-serif;">🎮 게임랜드</a>
    </h3>

    <ul id="top_menu">
        <?php if (!$userid) { ?>
            <li><a href="member_form.html">회원가입</a></li>
            <li> | </li>
            <li><a href="login_form.html">로그인</a></li>
        <?php } else { 
            // 로그인된 상태
            if ($userlevel == 1) { ?>
                <li><a href="admin.html">관리자 모드</a></li>
                <li> | </li>
            <?php } 
            $logged = htmlspecialchars($username) . "(" . htmlspecialchars($userid) . ")님 환영합니다.";
            ?>
            <li><?= $logged ?></li>
            <li> | </li>
            <li><a href="logout.html">로그아웃</a></li>
            <li> | </li>
            <li><a href="member_modify_form.html">정보수정</a></li>
        <?php } ?>
    </ul>
</div>

<nav id="menu_bar">
    <ul>
        <li><a href="index.html">홈</a></li>
        <li><a href="board_list.html">게시판</a></li>
        <li><a href="tetris.html">테트리스</a></li>
        <li><a href="apple_game.html">사과 게임</a></li>
        <li><a href="2048.html">2048 게임</a></li>
        <li><a href="message_form.html">고객센터</a></li>
    </ul>
</nav>
