<?php
session_start();
$userid = $_SESSION["userid"] ?? "";
$username = $_SESSION["username"] ?? "";
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8" />
  <title>2048 - 게임랜드</title>
  <link rel="stylesheet" href="./css/common.css" />
  <link rel="stylesheet" href="./css/2048.css" />
</head>
<body>
<header>
  <?php include "header.php"; ?>
</header>

<section>
  <div id="main_img_bar">
    <img src="./img/main_img.png" alt="메인 이미지">
  </div>
</section>

<section id="lobby">
  <h1>🧮 2048 🧮</h1>
  <p>방향키로 타일을 움직여 점수를 높이세요!</p>
  <button id="startGameButton" class="button">게임 시작</button>
  <a href="index.php" class="button">돌아가기</a>
  <p>조작키 : ↑ ↓ ← → 방향키</p>
  <!-- 볼륨 조절 메뉴 -->
    <div id="volumeControl" style="margin-top: 30px;">
      <h3>소리 설정 🎵</h3>
      <label for="bgmVolume">배경음:</label>
      <input type="range" id="bgmVolume" min="0" max="1" step="0.01" value="1" />
    </div>
  </section>
</section>

<section id="gameSection" class="hidden">
  <h1>2048 게임</h1>
  <p>방향키로 타일을 움직여 보세요!</p>
  <div id="score">점수: 0</div>
  <div id="grid"></div>
  <button id="backToLobbyButton">로비로 돌아가기</button>
</section>

<footer>
  <?php include "footer.php"; ?>
</footer>

<script>
const startGameButton = document.getElementById("startGameButton");
const backToLobbyButton = document.getElementById("backToLobbyButton");
const lobby = document.getElementById("lobby");
const gameSection = document.getElementById("gameSection");
const grid = document.getElementById("grid");
const scoreDisplay = document.getElementById("score");

const SIZE = 4;
let board = [];
let score = 0;
let gameOverFlag = false;

function initBoard() {
  board = Array.from({length: SIZE}, () => Array(SIZE).fill(0));
  score = 0;
  gameOverFlag = false;
  addRandomTile();
  addRandomTile();
  updateUI();
  updateScore();
}

function addRandomTile() {
  const emptyPositions = [];
  for(let r=0; r<SIZE; r++) {
    for(let c=0; c<SIZE; c++) {
      if(board[r][c] === 0) emptyPositions.push([r,c]);
    }
  }
  if(emptyPositions.length === 0) return false;
  const [r, c] = emptyPositions[Math.floor(Math.random()*emptyPositions.length)];
  board[r][c] = Math.random() < 0.9 ? 2 : 4;
  return true;
}

function updateUI() {
  grid.innerHTML = "";
  for(let r=0; r<SIZE; r++) {
    for(let c=0; c<SIZE; c++) {
      const val = board[r][c];
      const div = document.createElement("div");
      div.className = "tile";
      if(val > 0) div.textContent = val;
      div.setAttribute("data-value", val);
      grid.appendChild(div);
    }
  }
}

function slide(row) {
  let arr = row.filter(v => v !== 0);
  for(let i=0; i<arr.length-1; i++) {
    if(arr[i] === arr[i+1]) {
      arr[i] *= 2;
      score += Math.log2(arr[i]) * 0.1;
      score = Math.round(score * 10) / 10;  // 소수점 1자리로 반올림해서 유지
      arr[i+1] = 0;
    }
  }
  arr = arr.filter(v => v !== 0);
  while(arr.length < SIZE) arr.push(0);
  return arr;
}

function updateScore() {
  scoreDisplay.textContent = "점수: " + score.toFixed(1);
}


function rotateLeft(matrix) {
  const N = matrix.length;
  const ret = Array.from({length: N}, () => Array(N).fill(0));
  for(let r=0; r<N; r++) {
    for(let c=0; c<N; c++) {
      ret[N-1-c][r] = matrix[r][c];
    }
  }
  return ret;
}

function move(direction) {
  // direction: "left", "right", "up", "down"
  // 회전 변환으로 모두 left 이동으로 변환 처리
  let rotated = board;
  let rotatedCount = 0;

  switch(direction) {
    case "up": 
      rotatedCount = 1; 
      break;
    case "right":
      rotatedCount = 2; 
      break;
    case "down":
      rotatedCount = 3; 
      break;
    case "left":
      rotatedCount = 0; 
      break;
  }

  for(let i=0; i<rotatedCount; i++) {
    rotated = rotateLeft(rotated);
  }

  let moved = false;
  let newBoard = [];
  for(let r=0; r<SIZE; r++) {
    const newRow = slide(rotated[r]);
    if(newRow.some((v,i) => v !== rotated[r][i])) moved = true;
    newBoard.push(newRow);
  }

  // 역회전
  for(let i=0; i<(4 - rotatedCount) % 4; i++) {
    newBoard = rotateLeft(newBoard);
  }

  if(moved) {
    board = newBoard;
    addRandomTile();
    updateUI();
    updateScore();
    if(checkGameOver()) {
      alert("게임 종료! 점수: " + score);
      saveScore(score);
      showLobby();
      bgm.pause();
      bgm.currentTime = 0;
    }
  }
}

function checkGameOver() {
  // 빈칸 있으면 false
  for(let r=0; r<SIZE; r++) {
    for(let c=0; c<SIZE; c++) {
      if(board[r][c] === 0) return false;
    }
  }
  // 좌우상하 인접 동일값 있으면 false
  for(let r=0; r<SIZE; r++) {
    for(let c=0; c<SIZE; c++) {
      const val = board[r][c];
      if(c < SIZE-1 && board[r][c+1] === val) return false;
      if(r < SIZE-1 && board[r+1][c] === val) return false;
    }
  }
  return true;
}

function saveScore(score) {
  fetch("save_score.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `score=${encodeURIComponent(score)}&game_name=even`
  })
  .then(res => {
    if (!res.ok) throw new Error("서버 오류");
    return res.json();  // JSON이 반환되지 않으면 여기서 에러 발생
  })
  .then(data => {
    if (data?.message) alert(data.message);  // 점수 갱신 메시지 출력
  })
  .catch(err => {
    console.error("fetch 에러:", err);
  });
}

function showLobby() {
  gameSection.classList.add("hidden");
  lobby.classList.remove("hidden");
}

function startGame() {
  lobby.classList.add("hidden");
  gameSection.classList.remove("hidden");
  initBoard();
  window.addEventListener("keydown", onKeyDown);
  const bgm = document.getElementById("bgm2048");
  bgm.currentTime = 0;
  bgm.play();

}

function onKeyDown(e) {
  if(gameOverFlag) return;

  if (["ArrowLeft", "ArrowRight", "ArrowUp", "ArrowDown"].includes(e.key)) {
    e.preventDefault(); 
    
    switch(e.key) {
      case "ArrowLeft":
        move("left");
        break;
      case "ArrowRight":
        move("right");
        break;
      case "ArrowUp":
        move("up");
        break;
      case "ArrowDown":
        move("down");
        break;
    }
  }
}

startGameButton.addEventListener("click", startGame);

backToLobbyButton.addEventListener("click", () => {
  if(confirm("게임을 중단하고 로비로 돌아가시겠습니까? 점수는 저장되지 않습니다.")) {
    gameOverFlag = true;
    showLobby();
    window.removeEventListener("keydown", onKeyDown);
  }
});
document.addEventListener("DOMContentLoaded", () => {
  const bgm = document.getElementById("bgm2048");

  const bgmVolumeControl = document.getElementById("bgmVolume");

  // 초기값 세팅 (슬라이더 값 -> 오디오 볼륨)
  bgm.volume = bgmVolumeControl.value;

  bgmVolumeControl.addEventListener("input", (e) => {
  bgm.volume = e.target.value;
  });
});
</script>
<audio id="bgm2048" src="audio/bgm_2048.mp3" loop></audio>
</body>
</html>
