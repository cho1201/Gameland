<!DOCTYPE html>
<html>
<head> 
    <meta charset="utf-8">
    <title>게임랜드</title>
    <link rel="stylesheet" type="text/css" href="./css/common.css">
    <link rel="stylesheet" type="text/css" href="./css/main.css">
</head>
<body> 

<section>
    <div id="main_img_bar">
        <img src="./img/main_img.png">
    </div>

    <div id="main_content">
        <!-- 공지사항 -->
        <div id="latest">
            <h4>공지사항</h4>
            <ul>
            <?php
                $con = mysqli_connect("localhost", "user1", "12345", "term_project");
                $sql = "SELECT * FROM notice ORDER BY num DESC LIMIT 5";
                $result = mysqli_query($con, $sql);

                if (!$result) {
                    echo "<li>공지사항 DB 테이블(notice)이 생성 전이거나 아직 게시글이 없습니다!</li>";
                } else {
                    while ($row = mysqli_fetch_array($result)) {
                        $subject = htmlspecialchars($row["subject"]);
                        $name = htmlspecialchars($row["name"]);
                        $regist_day = substr($row["regist_day"], 0, 10);
                        echo "<li><span>$subject</span><span>$name</span><span>$regist_day</span></li>";
                    }
                }
            ?>
            </ul>
        </div>

        <!-- 포인트 랭킹 -->
        <div id="point_rank">
            <h4>포인트 랭킹</h4>
            <ul>
            <?php
                $rank = 1;
                $sql = "select * from members order by point desc limit 5";
                $result = mysqli_query($con, $sql);

                if (!$result) {
                    echo "<li>회원 DB 테이블(members)이 생성 전이거나 아직 가입된 회원이 없습니다!</li>";
                } else {
                    while ($row = mysqli_fetch_array($result)) {
                        $name = htmlspecialchars($row["name"]);
                        $id = htmlspecialchars($row["id"]);
                        $point = $row["point"];
                        $name_masked = mb_substr($name, 0, 1) . " * " . mb_substr($name, 2, 1);
                        echo "<li><span>$rank</span><span>$name_masked</span><span>$id</span><span>$point</span></li>";
                        $rank++;
                    }
                }

                mysqli_close($con);
            ?>
            </ul>
        </div>
    </div>
</section> 

</body>
</html>
