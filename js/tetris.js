const canvas = document.getElementById("gameCanvas");
const ctx = canvas.getContext("2d");
const holdCanvas = document.getElementById("holdCanvas");
const holdCtx = holdCanvas.getContext("2d");
const nextCanvas = document.getElementById("nextCanvas");
const nextCtx = nextCanvas.getContext("2d");
const scoreDisplay = document.getElementById("score");
const levelDisplay = document.getElementById("level");
const startGameButton = document.getElementById("startGameButton");
const gameSection = document.getElementById("gameSection");
const lobby = document.getElementById("lobby");
const inGameBackToLobbyButton = document.getElementById("inGameBackToLobbyButton");

const COLS = 10, ROWS = 20, BLOCK_SIZE = 30;
const board = Array.from({ length: ROWS }, () => Array(COLS).fill(0));
const SHAPES = {
  I: [[1, 1, 1, 1]], J: [[1, 0, 0], [1, 1, 1]], L: [[0, 0, 1], [1, 1, 1]],
  O: [[1, 1], [1, 1]], S: [[0, 1, 1], [1, 1, 0]],
  T: [[0, 1, 0], [1, 1, 1]], Z: [[1, 1, 0], [0, 1, 1]],
};
const COLORS = {
  I: 'cyan', J: 'blue', L: 'orange', O: 'yellow',
  S: 'green', T: 'purple', Z: 'red'
};

let current, next, hold = null, holdUsed = false;
let x = 3, y = 0;
let score = 0, level = 1;
let dropInterval = 1000;
let dropCounter = 0, lastTime = 0;
let isGameOver = false;

function randomBlock() {
  const keys = Object.keys(SHAPES);
  const type = keys[Math.floor(Math.random() * keys.length)];
  return { type, shape: SHAPES[type] };
}

function resetBlock() {
  current = next || randomBlock();
  next = randomBlock();
  x = 3; y = 0; holdUsed = false;
  if (!canMove(current.shape, x, y)) gameOver();
  drawNextBlock();
}

function resetGame() {
  for (let row of board) row.fill(0);
  score = 0; level = 1;
  dropInterval = 1000;
  hold = null; holdUsed = false;
  isGameOver = false;
  scoreDisplay.textContent = score;
  levelDisplay.textContent = level;
  next = randomBlock();
  resetBlock();
  clearHoldCanvas();
  drawNextBlock();
}

function canMove(shape, xPos, yPos) {
  return shape.every((row, dy) =>
    row.every((val, dx) => {
      if (!val) return true;
      const newX = xPos + dx, newY = yPos + dy;
      return newX >= 0 && newX < COLS && newY < ROWS && (!board[newY][newX]);
    })
  );
}

function rotate(shape) {
  return shape[0].map((_, i) => shape.map(row => row[i]).reverse());
}

function merge() {
  current.shape.forEach((row, dy) => {
    row.forEach((val, dx) => {
      if (val) board[y + dy][x + dx] = current.type;
    });
  });
}

function drop() {
  if (canMove(current.shape, x, y + 1)) {
    y++;
  } else {
    merge();
    const lines = clearLines();
    score += [0, 1, 3, 8, 30][lines];
    scoreDisplay.textContent = score;
    level = score <= 20 ? 1 : score <= 50 ? 2 : score <= 70 ? 3 : score <= 100 ? 4 : 5;
    levelDisplay.textContent = level;
    dropInterval = 1000 * (1 - (level - 1) * 0.2);
    resetBlock();
  }
}

function drawBlock(shape, offsetX, offsetY, ctxRef, color) {
  ctxRef.fillStyle = color;
  shape.forEach((row, dy) => {
    row.forEach((val, dx) => {
      if (val) ctxRef.fillRect((offsetX + dx) * BLOCK_SIZE, (offsetY + dy) * BLOCK_SIZE, BLOCK_SIZE, BLOCK_SIZE);
    });
  });
}

function clearHoldCanvas() {
  holdCtx.clearRect(0, 0, holdCanvas.width, holdCanvas.height);
}

function drawHoldBlock() {
  clearHoldCanvas();
  if (!hold) return;
  drawBlock(hold.shape, 1, 1, holdCtx, COLORS[hold.type]);
}

function clearNextCanvas() {
  nextCtx.clearRect(0, 0, nextCanvas.width, nextCanvas.height);
}

function drawNextBlock() {
  clearNextCanvas();
  if (!next) return;
  drawBlock(next.shape, 1, 1, nextCtx, COLORS[next.type]);
}

function drawGrid() {
  ctx.strokeStyle = "#333";
  for (let r = 0; r < ROWS; r++) {
    for (let c = 0; c < COLS; c++) {
      ctx.strokeRect(c * BLOCK_SIZE, r * BLOCK_SIZE, BLOCK_SIZE, BLOCK_SIZE);
    }
  }
}

function getGhostY() {
  let ghostY = y;
  while (canMove(current.shape, x, ghostY + 1)) ghostY++;
  return ghostY;
}

function drawGhost() {
  const ghostY = getGhostY();
  ctx.globalAlpha = 0.3;
  drawBlock(current.shape, x, ghostY, ctx, COLORS[current.type]);
  ctx.globalAlpha = 1.0;
}

function draw() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  drawGrid();
  drawGhost();
  drawBlock(current.shape, x, y, ctx, COLORS[current.type]);
  for (let r = 0; r < ROWS; r++) {
    for (let c = 0; c < COLS; c++) {
      if (board[r][c]) {
        ctx.fillStyle = COLORS[board[r][c]];
        ctx.fillRect(c * BLOCK_SIZE, r * BLOCK_SIZE, BLOCK_SIZE, BLOCK_SIZE);
      }
    }
  }
  drawHoldBlock();
  drawNextBlock();
}

function saveScore(score) {
  fetch("save_score.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `score=${encodeURIComponent(score)}&game_name=tetris`
  })
  .then(res => res.json())
  .then(data => { if (data?.message) alert(data.message); })
  .catch(err => console.error("점수 저장 실패:", err));
}

function gameOver() {
  isGameOver = true;
  saveScore(score);
  showLobby();
}

function showLobby() {
  gameSection.classList.add("hidden");
  lobby.classList.remove("hidden");
}

function startGame() {
  lobby.classList.add("hidden");
  gameSection.classList.remove("hidden");
  resetGame();
  lastTime = performance.now();
  update();
  bgmTetris.play();
}

function clearLines() {
  let count = 0;
  outer: for (let r = ROWS - 1; r >= 0; r--) {
    for (let c = 0; c < COLS; c++) {
      if (!board[r][c]) continue outer;
    }
    board.splice(r, 1);
    board.unshift(Array(COLS).fill(0));
    count++; r++;
  }

  if (count > 0) {
    effectClearLine.currentTime = 0;
    effectClearLine.play();
  }
  return count;
}

function update(time = 0) {
  if (isGameOver) return;
  const deltaTime = time - lastTime;
  lastTime = time;
  dropCounter += deltaTime;

  if (dropCounter > dropInterval) {
    drop();
    dropCounter = 0;
  }

  draw();
  requestAnimationFrame(update);
}
document.addEventListener("keydown", e => {
  if (isGameOver) return;

  switch (e.key) {
    case "ArrowLeft":
    case "ArrowRight":
    case "ArrowDown":
    case "ArrowUp":
    case " ":
    case "c":
      e.preventDefault();  // 기본 스크롤 동작 방지
      break;
  }

  switch (e.key) {
    case "ArrowLeft":
      if (canMove(current.shape, x - 1, y)) x--;
      break;
    case "ArrowRight":
      if (canMove(current.shape, x + 1, y)) x++;
      break;
    case "ArrowDown":
      drop();
      dropCounter = 0;
      break;
    case "ArrowUp":
      const rotated = rotate(current.shape);
      if (canMove(rotated, x, y)) current.shape = rotated;
      break;
    case " ":
      while (canMove(current.shape, x, y + 1)) y++;
      drop();
      dropCounter = 0;
      break;
    case "c":
      if (!holdUsed) {
        if (!hold) {
          hold = { type: current.type, shape: current.shape };
          resetBlock();
        } else {
          [hold.type, current.type] = [current.type, hold.type];
          [hold.shape, current.shape] = [current.shape, hold.shape];
          x = 3; y = 0;
          if (!canMove(current.shape, x, y)) gameOver();
        }
        holdUsed = true;
        drawHoldBlock();
      }
      break;
  }
});

startGameButton.addEventListener("click", startGame);
inGameBackToLobbyButton.addEventListener("click", () => {
  if (confirm("게임을 중단하고 로비로 돌아가시겠습니까? 점수는 저장되지 않습니다.")) {
    isGameOver = true;
    showLobby();
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const bgmVolumeControl = document.getElementById("bgmVolume");
  const effectVolumeControl = document.getElementById("effectVolume");

  bgmTetris.volume = bgmVolumeControl.value;
  effectClearLine.volume = effectVolumeControl.value;

  bgmVolumeControl.addEventListener("input", (e) => {
    bgmTetris.volume = e.target.value;
  });
  effectVolumeControl.addEventListener("input", (e) => {
    effectClearLine.volume = e.target.value;
  });
});
