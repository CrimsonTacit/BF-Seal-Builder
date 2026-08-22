/* Small shared helpers. Plain script, no modules: hangs off window.StateKit.

   These were duplicated in all six tools — six copies of the same debounce,
   the same slug regex, the same PRNG. Nothing here is clever; the point is
   that a fix lands once. */
"use strict";
const StateKit = (() => {

  /* mulberry32: the seeded PRNG every starfield in the suite uses. Same seed,
     same sky, so a design's stars survive a reload and an export matches the
     preview. The starfields themselves are deliberately NOT shared — a seal's
     circle, a patch's triangle and a poster's page are different geometry, and
     folding them together would be false commonality. */
  function mulberry32(a){
    return function(){
      a |= 0; a = a + 0x6D2B79F5 | 0;
      let t = Math.imul(a ^ a >>> 15, 1 | a);
      t = t + Math.imul(t ^ t >>> 7, 61 | t) ^ t;
      return ((t ^ t >>> 14) >>> 0) / 4294967296;
    };
  }

  /* Filename-safe slug. Each tool picks its own source text; only the
     transform is shared. */
  function slugify(text, fallback){
    return String(text || fallback || "")
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, "-")
      .replace(/^-+|-+$/g, "") || (fallback || "untitled");
  }

  /* Debounced localStorage writer. Returns the function to call on any change.
     Writes are wrapped because Safari's private mode throws on setItem, and a
     failed save must never take the app down with it. */
  function persister(key, getState, delay){
    let t = null;
    return function(){
      clearTimeout(t);
      t = setTimeout(() => {
        try{ localStorage.setItem(key, JSON.stringify(getState())); }catch(e){}
      }, delay || 150);
    };
  }

  /* Read the newest saved state, falling back through older keys so an
     existing design survives a storage-shape bump. Returns null if nothing
     is stored or the payload is unreadable. */
  function loadState(key, olderKeys){
    try{
      let raw = localStorage.getItem(key);
      for(const k of (olderKeys || [])){ if(!raw) raw = localStorage.getItem(k); }
      return raw ? JSON.parse(raw) : null;
    }catch(e){ return null; }
  }

  return { mulberry32, slugify, persister, loadState };
})();
