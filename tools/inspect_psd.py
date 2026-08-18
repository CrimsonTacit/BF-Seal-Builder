#!/usr/bin/env python3
"""Print a PSD's layer tree without opening Photoshop or compositing pixels.

    python3 tools/inspect_psd.py "examples/Plaques/NewPlaqueDesign copy.psd"
    python3 tools/inspect_psd.py "examples/Banners/BF-banners copy.psd" --effects

Needs only `psd-tools` (already installed for extract_plaque_assets.py) —
no PIL/numpy/scikit-image, no [composite] extra. Reading the layer tree is
cheap; it's compositing/rendering that's the heavy part, and this script
never does that. Use it to find layer/group names and check for effects
(bevel, gradient overlay, stroke, drop shadow, satin) before hand-tracing
anything from a screenshot or reference PNG.
"""
import argparse
import sys
from pathlib import Path

from psd_tools import PSDImage

def describe_effects(layer):
    effects = getattr(layer, "effects", None)
    if not effects:
        return []
    lines = []
    # psd-tools' Effects object is iterable and yields effect instances directly.
    try:
        for fx in effects:
            enabled = getattr(fx, "enabled", True)
            name = type(fx).__name__
            detail_bits = []
            for field in ("opacity", "blend_mode", "size", "angle", "distance",
                           "altitude", "depth", "highlight_color", "shadow_color",
                           "color"):
                if hasattr(fx, field):
                    try:
                        detail_bits.append(f"{field}={getattr(fx, field)}")
                    except Exception:
                        pass
            flag = "" if enabled else " [disabled]"
            lines.append(f"{name}{flag} ({', '.join(detail_bits)})")
    except TypeError:
        pass
    return lines


def walk(layer, depth, show_effects):
    indent = "  " * depth
    kind = getattr(layer, "kind", "?")
    vis = "" if layer.is_visible() else " [hidden]"
    bbox = layer.bbox if layer.bbox != (0, 0, 0, 0) else None
    dims = f" {bbox[2]-bbox[0]}x{bbox[3]-bbox[1]} @({bbox[0]},{bbox[1]})" if bbox else ""
    print(f"{indent}- {layer.name!r} [{kind}]{dims}{vis}")

    if show_effects:
        for line in describe_effects(layer):
            print(f"{indent}    fx: {line}")

    if layer.is_group():
        for child in layer:
            walk(child, depth + 1, show_effects)


def main():
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("psd", help="path to a .psd file")
    ap.add_argument("--effects", action="store_true", help="also print layer effects (bevel, gradient overlay, stroke, etc.)")
    ap.add_argument("--find", help="only print layers/groups whose name contains this (case-insensitive)")
    args = ap.parse_args()

    path = Path(args.psd)
    if not path.exists():
        sys.exit(f"no such file: {path}")

    psd = PSDImage.open(path)
    print(f"{path.name}  {psd.width}x{psd.height}  {len(list(psd))} top-level layers")
    print()

    if args.find:
        needle = args.find.lower()
        def matches(layer):
            if needle in layer.name.lower():
                return True
            return layer.is_group() and any(matches(c) for c in layer)
        for layer in psd:
            if matches(layer):
                walk(layer, 0, args.effects)
    else:
        for layer in psd:
            walk(layer, 0, args.effects)


if __name__ == "__main__":
    main()
