<?php
session_start();
$pageTitle = "Box‑Breathing Trainer";
include "../../../system/includes/header.php";
?>

<div class="container mx-auto px-4 py-8">
  <div class="max-w-4xl mx-auto space-y-8">
    <!-- Back to Home Link -->
    <div class="flex justify-start">
      <a href="/mp/" class="inline-flex items-center gap-2 px-4 py-2 bg-secondary text-secondary-foreground rounded-lg hover:bg-secondary/90 transition-colors font-medium">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="m12 19-7-7 7-7"/>
          <path d="M19 12H5"/>
        </svg>
        Back to Home
      </a>
    </div>
    
    <!-- Page Header -->
    <div class="text-center space-y-4">
      <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold">Box‑Breathing Trainer</h1>
      <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
        Interactive breathing exercise trainer to help you relax and focus through controlled breathing patterns
      </p>
    </div>

    <!-- Main Content -->
    <div class="bg-card text-card-foreground rounded-lg border p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6 lg:space-y-8">
      <!-- Controls -->
      <div id="controls" class="space-y-3 sm:space-y-4 lg:space-y-6">
        <h2 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Breathing Settings</h2>
        
        <!-- Rounds -->
        <div data-key="rounds" class="flex items-center justify-between gap-2 sm:gap-4 p-3 sm:p-4 bg-muted/50 rounded-lg">
          <span class="font-medium text-sm sm:text-base">Rounds</span>
          <div class="flex items-center gap-1 sm:gap-2">
            <button class="p-1 sm:p-2 rounded-lg hover:bg-accent transition-colors" data-action="dec" aria-label="rounds minus">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" sm:width="20" sm:height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus"><line x1="5" x2="19" y1="12" y2="12"/></svg>
            </button>
            <span class="value w-8 sm:w-12 text-lg sm:text-xl text-center select-none font-semibold">4</span>
            <button class="p-1 sm:p-2 rounded-lg hover:bg-accent transition-colors" data-action="inc" aria-label="rounds plus">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" sm:width="20" sm:height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
            </button>
          </div>
        </div>

        <!-- Each Phase -->
        <?php
          $rows = [
            ["key"=>"inhale",    "label"=>"Inhale"],
            ["key"=>"holdFull",  "label"=>"Hold ↑"],
            ["key"=>"exhale",    "label"=>"Exhale"],
            ["key"=>"holdEmpty", "label"=>"Hold ↓"],
          ];
          foreach ($rows as $r) {
            echo <<<HTML
              <div data-key="{$r['key']}" class="flex items-center justify-between gap-2 sm:gap-4 p-3 sm:p-4 bg-muted/50 rounded-lg">
                <span class="font-medium text-sm sm:text-base">{$r['label']}</span>
                <div class="flex items-center gap-1 sm:gap-2">
                  <button class="p-1 sm:p-2 rounded-lg hover:bg-accent transition-colors" data-action="dec" aria-label="{$r['label']} minus">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" sm:width="20" sm:height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus"><line x1="5" x2="19" y1="12" y2="12"/></svg>
                  </button>
                  <span class="value w-8 sm:w-12 text-lg sm:text-xl text-center select-none font-semibold">4</span>
                  <button class="p-1 sm:p-2 rounded-lg hover:bg-accent transition-colors" data-action="inc" aria-label="{$r['label']} plus">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" sm:width="20" sm:height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                  </button>
                </div>
              </div>
            HTML;
          }
        ?>
      </div>

      <!-- Timer Display -->
      <div class="flex flex-col items-center gap-4 sm:gap-6 lg:gap-8 py-4 sm:py-6 lg:py-8">
        <div id="circle" class="w-48 h-48 sm:w-56 sm:h-56 lg:w-64 lg:h-64 rounded-full border-4 sm:border-6 lg:border-8 border-primary flex items-center justify-center transition-transform duration-300 scale-100 shadow-lg">
          <span id="timer" class="text-2xl sm:text-3xl lg:text-4xl xl:text-5xl font-bold select-none">4s</span>
        </div>
        <div class="text-center space-y-2 sm:space-y-3">
          <p class="text-lg sm:text-xl lg:text-2xl xl:text-3xl">
            Phase: <span id="phase" class="font-bold text-primary">Inhale</span>
          </p>
          <p class="text-base sm:text-lg lg:text-xl">
            Remaining: <span id="remaining" class="font-bold">4</span> rounds
          </p>
        </div>
      </div>

      <!-- Control Buttons -->
      <div class="flex justify-center gap-3 sm:gap-4 lg:gap-6">
        <button id="startPause"
                class="px-6 sm:px-8 lg:px-12 py-2 sm:py-3 lg:py-4 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors font-medium text-base sm:text-lg">
          Start&nbsp;(S)
        </button>
        <button id="reset"
                class="px-6 sm:px-8 lg:px-12 py-2 sm:py-3 lg:py-4 bg-secondary text-secondary-foreground rounded-lg hover:bg-secondary/90 transition-colors font-medium text-base sm:text-lg">
          Reset&nbsp;(R)
        </button>
      </div>
    </div>

    <!-- Instructions -->
    <div class="bg-muted/50 rounded-lg p-4 sm:p-6">
      <h3 class="text-base sm:text-lg font-semibold mb-2 sm:mb-3">💡 How to Use</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 text-xs sm:text-sm">
        <div>
          <h4 class="font-medium mb-1 sm:mb-2">Breathing Pattern:</h4>
          <ul class="space-y-1 text-muted-foreground">
            <li>• Inhale slowly through your nose</li>
            <li>• Hold your breath</li>
            <li>• Exhale slowly through your mouth</li>
            <li>• Hold with empty lungs</li>
          </ul>
        </div>
        <div>
          <h4 class="font-medium mb-1 sm:mb-2">Tips:</h4>
          <ul class="space-y-1 text-muted-foreground">
            <li>• Find a comfortable position</li>
            <li>• Focus on your breath</li>
            <li>• Use keyboard shortcuts (S/R)</li>
            <li>• Start with 4-second intervals</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(() => {
  /* 데이터 초기값 */
  const phases     = ["Inhale", "Hold ↑", "Exhale", "Hold ↓"];
  const phaseKeys  = ["inhale", "holdFull", "exhale", "holdEmpty"];
  let durations    = { inhale: 4, holdFull: 4, exhale: 4, holdEmpty: 4 };
  let rounds       = 4;

  /* DOM 요소 */
  const controlsEl   = document.getElementById("controls");
  const circle       = document.getElementById("circle");
  const timerSpan    = document.getElementById("timer");
  const phaseSpan    = document.getElementById("phase");
  const remainSpan   = document.getElementById("remaining");
  const startBtn     = document.getElementById("startPause");
  const resetBtn     = document.getElementById("reset");

  /* 상태 변수 */
  let running         = false;
  let currentPhaseIdx = 0;
  let timeLeft        = durations[phaseKeys[0]];
  let remainRounds    = rounds;
  let timerId         = null;

  /* 보조 함수 */
  const clamp = (n) => Math.max(1, n);

  function redraw() {
    timerSpan.textContent  = `${timeLeft}s`;
    phaseSpan.textContent  = phases[currentPhaseIdx];
    remainSpan.textContent = Math.max(remainRounds, 0);

    // 간단한 수축→팽창 효과
    circle.classList.remove("scale-75", "scale-100");
    void circle.offsetWidth;            // 강제 리플로우
    circle.classList.add("scale-75");
    setTimeout(() => circle.classList.replace("scale-75", "scale-100"), 40);
  }

  function tick() {
    timeLeft--;
    if (timeLeft <= 0) {
      currentPhaseIdx = (currentPhaseIdx + 1) % phases.length;
      if (currentPhaseIdx === 0) remainRounds--;

      if (remainRounds <= 0) { handleReset(); return; }
      timeLeft = durations[phaseKeys[currentPhaseIdx]];
    }
    redraw();
  }

  function handleStartPause() {
    running = !running;
    startBtn.textContent = running ? "Pause (S)" : "Start (S)";
    running ? timerId = setInterval(tick, 1000)
            : clearInterval(timerId);
  }

  function handleReset() {
    clearInterval(timerId);
    running         = false;
    startBtn.textContent = "Start (S)";
    currentPhaseIdx = 0;
    timeLeft        = durations.inhale;
    remainRounds    = rounds;
    redraw();
  }

  /* ① 버튼(＋/−)으로 값 변경 */
  controlsEl.addEventListener("click", (e) => {
    if (!e.target.closest("[data-action]")) return;
    const action = e.target.closest("[data-action]").dataset.action;
    const row      = e.target.closest("[data-key]");
    const key      = row.dataset.key;
    const valueEl  = row.querySelector(".value");
    let val        = parseInt(valueEl.textContent, 10);

    val += action === "inc" ? 1 : -1;
    val  = clamp(val);
    valueEl.textContent = val;

    if (key === "rounds") {
      rounds = val;
      if (!running) remainSpan.textContent = val;
    } else {
      durations[key] = val;
      if (!running && phaseKeys[currentPhaseIdx] === key) timeLeft = val;
    }
    if (!running) redraw();
  });

  /* ② 시작/멈춤·리셋 버튼 */
  startBtn.addEventListener("click", handleStartPause);
  resetBtn.addEventListener("click", handleReset);

  /* ③ S / R 단축키 */
  window.addEventListener("keydown", (e) => {
    if (e.key.toLowerCase() === "s") handleStartPause();
    if (e.key.toLowerCase() === "r") handleReset();
  });

  redraw();   // 초기 한 번 그리기
})();
</script>

<?php include "../../../system/includes/footer.php"; ?>