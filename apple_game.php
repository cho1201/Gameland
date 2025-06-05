<?php
session_start();
$userid = isset($_SESSION["userid"]) ? $_SESSION["userid"] : "";
$username = isset($_SESSION["username"]) ? $_SESSION["username"] : "";
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8" />
  <title>사과 게임 - 게임랜드</title>
  <link rel="stylesheet" href="./css/common.css" />
  <link rel="stylesheet" href="./css/apple_game.css" /> <!-- 새로 추가한 CSS -->
</head>
<body>
  <header>
    <?php include "header.php"; ?>
  </header>
  <section>
    <div id="main_img_bar">
      <img src="./img/main_img.png" />
    </div>
  </section>
  <!-- 로비 화면 -->
  <section id="lobby">
    <h1>🍎 사과 게임 🍎</h1>
    <p>숫자의 합이 10이 되도록 드래그해서 점수를 얻는 게임입니다!</p>
    <button id="startGameButton" class="button">게임 시작</button>
    <a href="index.php" class="button">돌아가기</a>
    <p>조작법 : 마우스 좌클릭</p>
    <!-- 볼륨 조절 메뉴 -->
    <div id="volumeControl" style="margin-top: 30px;">
      <h3>소리 설정 🎵</h3>
      <label for="bgmVolume">배경음:</label>
      <input type="range" id="bgmVolume" min="0" max="1" step="0.01" value="1" />
      <br />
      <label for="effectVolume">효과음:</label>
      <input type="range" id="effectVolume" min="0" max="1" step="0.01" value="1" />
    </div>
  </section>

  <!-- 게임 화면 -->
  <section id="gameSection" class="hidden">
    <h1>사과게임</h1>
    <canvas id="gameCanvas" width="950" height="500"></canvas>
    <button id="inGameBackToLobbyButton" class="button">로비로 가기</button>
    <button id="restartButton" class="button hidden">게임 다시 시작</button>
    <button id="backToLobbyButton" class="button hidden">홈으로 돌아가기</button>
  </section>
  <style>
    #startGameButton {
    margin-bottom: 50px;
  }
  </style>
  <footer>
    <?php include "footer.php"; ?>
  </footer>

  <script>
    const canvas = document.getElementById("gameCanvas");
    const ctx = canvas.getContext("2d");
    const restartButton = document.getElementById("restartButton");
    const startGameButton = document.getElementById("startGameButton");
    const gameSection = document.getElementById("gameSection");
    const lobby = document.getElementById("lobby");
    const backToLobbyButton = document.getElementById("backToLobbyButton");
    const inGameBackToLobbyButton = document.getElementById("inGameBackToLobbyButton");

    const CELL_SIZE = 50;
    const GRID_WIDTH = 17;
    const GRID_HEIGHT = 10;
    const UI_WIDTH = 100;

    let grid = [];
    let selectedCells = [];
    let score = 0;
    let timeLeft = 120;
    let gameOver = false;
    let isDragging = false;
    let intervalId = null;

    inGameBackToLobbyButton.addEventListener("click", () => {
      clearInterval(intervalId);       // 타이머 완전 중지
      gameOver = true;                 // 상태를 종료로 설정 (렌더링에도 반영됨)
      selectedCells = [];              // 선택된 셀 초기화
      isDragging = false;              // 드래그 상태 해제
      restartButton.classList.add("hidden"); // 게임오버 버튼 숨김
      backToLobbyButton.classList.add("hidden"); // 게임오버용 로비 버튼도 숨김
      inGameBackToLobbyButton.classList.add("hidden"); // 자신도 숨김
      gameSection.classList.add("hidden"); // 게임 섹션 숨김
      lobby.classList.remove("hidden");   // 로비 화면 표시

      // 배경음악 중지 추가
      const bgm = document.getElementById("bgmApple");
      bgm.pause();
      bgm.currentTime = 0;
    });

    function initGrid() {
      grid = [];
      for (let y = 0; y < GRID_HEIGHT; y++) {
        const row = [];
        for (let x = 0; x < GRID_WIDTH; x++) {
          row.push(Math.floor(Math.random() * 9) + 1);
        }
        grid.push(row);
      }
    }

    function drawGrid() {
      ctx.textAlign = "center";
      ctx.textBaseline = "middle";
      ctx.font = "20px Arial";

      for (let y = 0; y < GRID_HEIGHT; y++) {
        for (let x = 0; x < GRID_WIDTH; x++) {
          ctx.strokeStyle = "white";
          ctx.strokeRect(x * CELL_SIZE, y * CELL_SIZE, CELL_SIZE, CELL_SIZE);
          if (grid[y][x] !== 0) {
            ctx.fillStyle = "white";
            ctx.fillText(grid[y][x], x * CELL_SIZE + CELL_SIZE / 2, y * CELL_SIZE + CELL_SIZE / 2);
          }
        }
      }
    }

    function drawSelectedCells() {
      ctx.strokeStyle = "blue";
      ctx.lineWidth = 3;
      for (const [x, y] of selectedCells) {
        ctx.strokeRect(x * CELL_SIZE, y * CELL_SIZE, CELL_SIZE, CELL_SIZE);
      }
    }

    function drawUI() {
      const uiX = GRID_WIDTH * CELL_SIZE;

      ctx.fillStyle = "white";
      ctx.fillRect(uiX, 0, UI_WIDTH, canvas.height);

      ctx.fillStyle = "black";
      ctx.font = "24px Arial";
      ctx.textAlign = "center";
      ctx.fillText("Score:", uiX + UI_WIDTH / 2, 30);
      ctx.fillText(score, uiX + UI_WIDTH / 2, 60);

      let minutes = Math.floor(timeLeft / 60);
      let seconds = timeLeft % 60;
      let timeString = `${minutes}:${seconds.toString().padStart(2, "0")}`;
      ctx.fillText("Time:", uiX + UI_WIDTH / 2, 110);
      ctx.fillText(timeString, uiX + UI_WIDTH / 2, 140);

      const barMaxHeight = 300;
      const barWidth = 20;
      const barX = uiX + (UI_WIDTH - barWidth) / 2;
      const barY = 180;

      const percentLeft = timeLeft / 120;
      const barHeight = barMaxHeight * percentLeft;

      ctx.fillStyle = "gray";
      ctx.fillRect(barX, barY, barWidth, barMaxHeight);

      ctx.fillStyle = "red";
      ctx.fillRect(barX, barY + (barMaxHeight - barHeight), barWidth, barHeight);
    }

    function getCellFromMouse(e) {
      const rect = canvas.getBoundingClientRect();
      const mouseX = e.clientX - rect.left;
      const mouseY = e.clientY - rect.top;

      if (mouseX < GRID_WIDTH * CELL_SIZE) {
        const gridX = Math.floor(mouseX / CELL_SIZE);
        const gridY = Math.floor(mouseY / CELL_SIZE);
        return [gridX, gridY];
      }
      return null;
    }

    function getSumOfSelectedCells() {
      return selectedCells.reduce((sum, [x, y]) => sum + grid[y][x], 0);
    }

    function checkAndClearIfSumTen() {
      const sum = getSumOfSelectedCells();
      if (sum === 10) {
        for (const [x, y] of selectedCells) {
          grid[y][x] = 0;
        }
        score += selectedCells.length;

        // 효과음 1회 재생
        const effect = document.getElementById("effectApple");
        effect.currentTime = 0;
        effect.play();
      }
      selectedCells = [];
    }

    let dragStartCell = null;
    let dragCurrentCell = null;

    function render() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      drawGrid();
      drawUI();
      drawSelectedCells();

      if (isDragging && dragStartCell && dragCurrentCell) {
        const [x1, y1] = dragStartCell;
        const [x2, y2] = dragCurrentCell;

        const minX = Math.min(x1, x2);
        const maxX = Math.max(x1, x2);
        const minY = Math.min(y1, y2);
        const maxY = Math.max(y1, y2);

        ctx.fillStyle = "rgba(0, 120, 255, 0.3)";
        ctx.strokeStyle = "rgba(0, 120, 255, 0.8)";
        ctx.lineWidth = 4;

        for (let y = minY; y <= maxY; y++) {
          for (let x = minX; x <= maxX; x++) {
            ctx.strokeRect(x * CELL_SIZE + 2, y * CELL_SIZE + 2, CELL_SIZE - 4, CELL_SIZE - 4);
          }
        }
      }

      if (gameOver) {
        ctx.fillStyle = "rgba(0, 0, 0, 0.5)";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = "white";
        ctx.font = "40px Arial";
        ctx.fillText("Game Over", canvas.width / 2, canvas.height / 2);
        restartButton.classList.remove("hidden");
        backToLobbyButton.classList.remove("hidden");
      }
    }

    function startGame() {
      // 로비 숨기고 게임 영역 보여줌
      lobby.classList.add("hidden");
      gameSection.classList.remove("hidden");
      inGameBackToLobbyButton.classList.remove("hidden");

      if (intervalId) clearInterval(intervalId);

      initGrid();
      gameOver = false;
      timeLeft = 120;
      score = 0; 
      selectedCells = [];
      restartButton.classList.add("hidden");
      backToLobbyButton.classList.add("hidden");

      // 배경음악 무한 반복 재생 시작
      const bgm = document.getElementById("bgmApple");
      bgm.currentTime = 0;
      bgm.play();

      intervalId = setInterval(() => {
        if (!gameOver) {
          timeLeft--;
          if (timeLeft <= 0) {
            gameOver = true;
            saveScore(score);
            // 게임 종료 시 배경음악 정지
            bgm.pause();
            bgm.currentTime = 0;
          }
          render();
        }
      }, 1000);

      render();
    }


    canvas.addEventListener("mousedown", (e) => {
      if (gameOver) return;
      const cell = getCellFromMouse(e);
      if (cell && grid[cell[1]][cell[0]] !== 0) {
        dragStartCell = cell;
        dragCurrentCell = cell;
        isDragging = true;
        selectedCells = [];
        render();
      }
    });

    canvas.addEventListener("mousemove", (e) => {
      if (isDragging) {
        const cell = getCellFromMouse(e);
        if (cell) {
          dragCurrentCell = cell;
          render();
        }
      }
    });

    canvas.addEventListener("mouseup", (e) => {
      if (!isDragging || !dragStartCell) return;
      isDragging = false;

      const cell = getCellFromMouse(e);
      if (cell) {
        const [x1, y1] = dragStartCell;
        const [x2, y2] = cell;

        const minX = Math.min(x1, x2);
        const maxX = Math.max(x1, x2);
        const minY = Math.min(y1, y2);
        const maxY = Math.max(y1, y2);

        selectedCells = [];

        for (let y = minY; y <= maxY; y++) {
          for (let x = minX; x <= maxX; x++) {
            if (grid[y][x] !== 0) {
              selectedCells.push([x, y]);
            }
          }
        }

        checkAndClearIfSumTen();
        dragStartCell = null;
        dragCurrentCell = null;
        render();
      }
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "i") {
        timeLeft = 3;
        render();
      }
    });

    restartButton.addEventListener("click", startGame);
    startGameButton.addEventListener("click", startGame);
    backToLobbyButton.addEventListener("click", () => {
      window.location.href = "index.php";
    });

    function saveScore(score) {
      fetch("save_score.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `score=${encodeURIComponent(score)}&game_name=apple`
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
    document.addEventListener("DOMContentLoaded", () => {
      const bgm = document.getElementById("bgmApple");
      const effect = document.getElementById("effectApple");

      const bgmVolumeControl = document.getElementById("bgmVolume");
      const effectVolumeControl = document.getElementById("effectVolume");

      // 초기값 세팅 (슬라이더 값 -> 오디오 볼륨)
      bgm.volume = bgmVolumeControl.value;
      effect.volume = effectVolumeControl.value;

      bgmVolumeControl.addEventListener("input", (e) => {
        bgm.volume = e.target.value;
      });

      effectVolumeControl.addEventListener("input", (e) => {
        effect.volume = e.target.value;
      });
  });
  </script>
<audio id="bgmApple" src="audio/bgm_apple.mp3" loop></audio>
<audio id="effectApple" src="audio/effect_apple.mp3"></audio>
</body>
</html>
