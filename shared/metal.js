/* Metal rendering helpers shared by banner-tool.html and plaque-tool.html.
   Extracted copy of the header-tool.html recipe (kept in sync by hand — the
   seal/header tools stay single-file and don't load this).
   Plain script, no modules: everything hangs off window.Metal. */
"use strict";
const Metal = (() => {

  /* Named multi-stop vertical ramps with a hard mid "horizon" break. */
  const MATERIALS = {
    gold:     { name:"Gold",       base:"#c9992e", edge:"#63470b",
                stops:[[0,"#fdf6bd"],[.28,"#f3d356"],[.47,"#c9992e"],[.5,"#8a6410"],[.53,"#a87c17"],[.74,"#e8c14a"],[1,"#fdf0a2"]] },
    silver:   { name:"Silver",     base:"#a6b0bc", edge:"#4c545e",
                stops:[[0,"#ffffff"],[.28,"#dfe4ea"],[.47,"#a6b0bc"],[.5,"#79828e"],[.53,"#8d97a3"],[.74,"#d4dae1"],[1,"#f6f8fa"]] },
    platinum: { name:"Platinum",   base:"#c9cdc4", edge:"#63665c",
                stops:[[0,"#ffffff"],[.28,"#eceee7"],[.47,"#c1c5ba"],[.5,"#969b8c"],[.53,"#a8ad9f"],[.74,"#e2e4dd"],[1,"#fafaf7"]] },
    copper:   { name:"Copper",     base:"#b45f2a", edge:"#54270c",
                stops:[[0,"#ffd9c0"],[.28,"#e89a63"],[.47,"#b45f2a"],[.5,"#7c3a12"],[.53,"#984f1e"],[.74,"#dd9258"],[1,"#ffcfae"]] },
    bronze:   { name:"Bronze",     base:"#96702c", edge:"#40300c",
                stops:[[0,"#f2e0b6"],[.28,"#cfa356"],[.47,"#96702c"],[.5,"#644312"],[.53,"#7d5a1e"],[.74,"#c49b4e"],[1,"#ecd9a8"]] },
    steel:    { name:"Steel Blue", base:"#8299b3", edge:"#2e3f50",
                stops:[[0,"#eef4fb"],[.28,"#c3d2e2"],[.47,"#8299b3"],[.5,"#5a7188"],[.53,"#6d849b"],[.74,"#b7c9db"],[1,"#e8f0f8"]] },
    gunmetal: { name:"Gunmetal",   base:"#4a525d", edge:"#14171c",
                stops:[[0,"#b9c0c9"],[.28,"#79828e"],[.47,"#4a525d"],[.5,"#30363f"],[.53,"#3d444e"],[.74,"#6d7681"],[1,"#a7aeb8"]] },
  };

  /* ---- color math ---- */
  function hexRgb(h){h=h.replace("#","");return [parseInt(h.slice(0,2),16),parseInt(h.slice(2,4),16),parseInt(h.slice(4,6),16)];}
  function rgbHex(r,g,b){return "#"+[r,g,b].map(v=>Math.max(0,Math.min(255,Math.round(v))).toString(16).padStart(2,"0")).join("");}
  function mix(hex, target, t){
    const a=hexRgb(hex), b=hexRgb(target);
    return rgbHex(a[0]+(b[0]-a[0])*t, a[1]+(b[1]-a[1])*t, a[2]+(b[2]-a[2])*t);
  }
  const ltn=(h,t)=>mix(h,"#ffffff",t), dkn=(h,t)=>mix(h,"#000000",t);

  /* Ramp generated from an arbitrary base color (for custom materials). */
  function rampFor(base){
    return [[0,ltn(base,.72)],[.28,ltn(base,.32)],[.47,base],[.5,dkn(base,.42)],[.53,dkn(base,.25)],[.74,ltn(base,.18)],[1,ltn(base,.6)]];
  }
  /* Material lookup: named key, or any "#rrggbb" treated as a custom base. */
  function matInfo(key){
    if(MATERIALS[key]) return MATERIALS[key];
    const base = /^#/.test(key) ? key : MATERIALS.gold.base;
    return { name:"Custom", base, edge: dkn(base,.62), stops: rampFor(base) };
  }

  function gradientDef(id, stops, y1, y2){
    return `<linearGradient id="${id}" x1="0" y1="${y1}" x2="0" y2="${y2}" gradientUnits="userSpaceOnUse">`
      + stops.map(s=>`<stop offset="${s[0]}" stop-color="${s[1]}"/>`).join("")
      + `</linearGradient>`;
  }

  /* Raised-relief filter: optional brushed streaks -> specular bevel -> shadow.
     opts: bevel (px-ish), shine (0..~1.2), se (specular exponent), tex (0..1),
     texFreq ("fx fy"), seed, shadow (0..1), shadowDy/shadowBlur. */
  function bevelFilter(id, opts){
    const o = Object.assign({bevel:3, shine:.9, se:22, tex:0, texFreq:"0.9 0.012",
                             seed:7, shadow:.3, shadowDy:4, shadowBlur:5}, opts);
    const bev = Math.max(0.01, o.bevel);
    let f = `<filter id="${id}" x="-20%" y="-50%" width="140%" height="200%" color-interpolation-filters="sRGB">`;
    if(o.tex > 0){
      const amp=(o.tex*.5).toFixed(2), off=(0.5-o.tex*.25).toFixed(2);
      f += `<feTurbulence type="fractalNoise" baseFrequency="${o.texFreq}" numOctaves="2" seed="${o.seed}" result="tn"/>
        <feColorMatrix in="tn" type="matrix" values="${amp} 0 0 0 ${off}  ${amp} 0 0 0 ${off}  ${amp} 0 0 0 ${off}  0 0 0 0 1" result="texg"/>
        <feBlend in="SourceGraphic" in2="texg" mode="overlay" result="txd"/>
        <feComposite in="txd" in2="SourceAlpha" operator="in" result="base"/>`;
    }else{
      f += `<feOffset in="SourceGraphic" dx="0" dy="0" result="base"/>`;
    }
    f += `<feGaussianBlur in="SourceAlpha" stdDeviation="${(bev*.7).toFixed(1)}" result="blur"/>
      <feSpecularLighting in="blur" surfaceScale="${(bev*1.2).toFixed(1)}" specularConstant="${(+o.shine).toFixed(2)}" specularExponent="${o.se}" lighting-color="#ffffff" result="spec">
        <feDistantLight azimuth="235" elevation="48"/>
      </feSpecularLighting>
      <feComposite in="spec" in2="SourceAlpha" operator="in" result="specM"/>
      <feComposite in="base" in2="specM" operator="arithmetic" k1="0" k2="1" k3="0.85" k4="0" result="lit"/>`;
    f += (o.shadow > 0)
      ? `<feDropShadow in="lit" dx="0" dy="${o.shadowDy}" stdDeviation="${o.shadowBlur}" flood-color="#000" flood-opacity="${(o.shadow*.7).toFixed(2)}"/>`
      : `<feOffset in="lit" dx="0" dy="0"/>`;
    return f + `</filter>`;
  }

  /* Recessed-relief (engraved) filter. Light still arrives from the upper
     left, so the cut's inner top-left edge falls into shadow and its inner
     bottom-right edge catches a highlight. Fill the element with a darkened
     backing color; this adds the rim shading.
     Pass `flatten` (a colour) when the source is artwork rather than text:
     the shape's own colours are replaced by that flat cut-in colour first.
     opts: depth (offset px), blur, dark (0..1), shine, se, flatten. */
  function engraveFilter(id, opts){
    const o = Object.assign({depth:5, blur:4, dark:.75, shine:.5, se:14, flatten:null}, opts);
    const src = o.flatten ? "flat" : "SourceGraphic";
    return `<filter id="${id}" x="-20%" y="-50%" width="140%" height="200%" color-interpolation-filters="sRGB">
      ${o.flatten ? `<feFlood flood-color="${o.flatten}" result="flatC"/>
      <feComposite in="flatC" in2="SourceAlpha" operator="in" result="flat"/>` : ""}
      <feComponentTransfer in="SourceAlpha" result="inv"><feFuncA type="table" tableValues="1 0"/></feComponentTransfer>
      <feOffset in="inv" dx="${o.depth}" dy="${o.depth}" result="invOff"/>
      <feGaussianBlur in="invOff" stdDeviation="${o.blur}" result="invBlur"/>
      <feFlood flood-color="#000" flood-opacity="${o.dark}" result="ink"/>
      <feComposite in="ink" in2="invBlur" operator="in" result="rimRaw"/>
      <feComposite in="rimRaw" in2="SourceAlpha" operator="in" result="rim"/>
      <feGaussianBlur in="SourceAlpha" stdDeviation="${(o.depth*.6).toFixed(1)}" result="soft"/>
      <feSpecularLighting in="soft" surfaceScale="-${(o.depth*1.1).toFixed(1)}" specularConstant="${(+o.shine).toFixed(2)}" specularExponent="${o.se}" lighting-color="#ffffff" result="spec">
        <feDistantLight azimuth="235" elevation="42"/>
      </feSpecularLighting>
      <feComposite in="spec" in2="SourceAlpha" operator="in" result="specM"/>
      <feMerge><feMergeNode in="${src}"/><feMergeNode in="rim"/><feMergeNode in="specM"/></feMerge>
    </filter>`;
  }

  function esc(s){return String(s).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;");}

  return { MATERIALS, hexRgb, rgbHex, mix, ltn, dkn, rampFor, matInfo,
           gradientDef, bevelFilter, engraveFilter, esc };
})();
