/* Stage.* — the canvas background behind the artwork, shared by all six tools.

   Every tool used to carry its own copy of this and expose a different subset
   of the same idea: the seal had dark/light plus a grid overlay, the header
   checker/dark/light plus an image upload, the poster dark/grey/light, and the
   other three checker/dark/light. Four of the six copies were byte-identical.
   This owns the whole thing, so every tool offers every option.

   The paint lives in shared/chrome.css with the rest of the shared chrome;
   this file is only the behaviour. It builds the button bar, owns the grid
   overlay, and persists the choice. It does not lay the stage out — flex,
   padding and the artwork wrap stay per-tool, since those genuinely differ
   (the plaque pads, the poster's wrap fills its box).

   Usage, once #stage and an empty #stageBg exist:  Stage.init();            */
"use strict";
window.Stage = (function(){

/* One key for the suite, not one per tool: the stage is app chrome, so a
   choice made in the seal tool should still be there in the poster tool. */
const KEY      = "bfgraphics-stagebg";
const GRID_KEY = "bfgraphics-stagegrid";

const MODES = [
  { id:"checker", label:"Checker" },
  { id:"dark",    label:"Dark"    },
  { id:"grey",    label:"Grey"    },
  { id:"light",   label:"Light"   },
];
/* "image" is a mode but not a preset: it has no paint of its own, it wears an
   uploaded file, and it can't be restored on boot (the file isn't kept). */
const ALL = MODES.map(m => m.id).concat("image");
const DEFAULT_MODE = "dark";

function el(id){ return typeof id === "string" ? document.getElementById(id) : id; }

function init(opts){
  opts = opts || {};
  const stage = el(opts.stage || "stage");
  const bar   = el(opts.bar   || "stageBg");
  if(!stage || !bar) return null;

  let mode = DEFAULT_MODE, grid = false;
  try{
    const m = localStorage.getItem(KEY);
    if(m && ALL.indexOf(m) >= 0 && m !== "image") mode = m;
    grid = localStorage.getItem(GRID_KEY) === "1";
  }catch(e){}

  /* The bar is built here rather than written into six HTML files, so adding a
     mode is one line in MODES instead of six edits. */
  bar.textContent = "";
  const btns = {};
  MODES.forEach(m => {
    const b = document.createElement("button");
    b.type = "button";
    b.dataset.mode = m.id;
    b.textContent = m.label;
    b.addEventListener("click", () => setMode(m.id));
    bar.appendChild(b);
    btns[m.id] = b;
  });

  const imgBtn = document.createElement("button");
  imgBtn.type = "button";
  imgBtn.dataset.mode = "image";
  imgBtn.textContent = "Image…";
  bar.appendChild(imgBtn);
  btns.image = imgBtn;

  const sep = document.createElement("span");
  sep.className = "sep";
  bar.appendChild(sep);

  const gridBtn = document.createElement("button");
  gridBtn.type = "button";
  gridBtn.textContent = "Grid Overlay";
  gridBtn.addEventListener("click", () => setGrid(!grid));
  bar.appendChild(gridBtn);

  const file = document.createElement("input");
  file.type = "file";
  file.accept = "image/*";
  file.style.display = "none";
  bar.appendChild(file);
  imgBtn.addEventListener("click", () => file.click());
  file.addEventListener("change", e => {
    const f = e.target.files[0];
    e.target.value = "";
    if(!f) return;
    const rd = new FileReader();
    rd.onload = () => {
      stage.style.backgroundImage = `url(${rd.result})`;
      setMode("image");
    };
    rd.readAsDataURL(f);
  });

  let overlay = stage.querySelector("#stageGrid");
  if(!overlay){
    overlay = document.createElement("div");
    overlay.id = "stageGrid";
    stage.appendChild(overlay);
  }

  function setMode(m){
    if(ALL.indexOf(m) < 0) return;
    /* Add/remove by name rather than assigning className: the stage may carry
       classes this module knows nothing about. */
    ALL.forEach(x => stage.classList.toggle(x, x === m));
    if(m !== "image") stage.style.backgroundImage = "";
    Object.keys(btns).forEach(k => btns[k].classList.toggle("active", k === m));
    mode = m;
    if(m !== "image"){ try{ localStorage.setItem(KEY, m); }catch(e){} }
  }

  function setGrid(on){
    grid = !!on;
    overlay.classList.toggle("on", grid);
    gridBtn.classList.toggle("active", grid);
    try{ localStorage.setItem(GRID_KEY, grid ? "1" : "0"); }catch(e){}
  }

  setMode(mode);
  setGrid(grid);
  return { setMode, setGrid, mode: () => mode, grid: () => grid };
}

return { init, MODES, KEY, GRID_KEY };
})();
