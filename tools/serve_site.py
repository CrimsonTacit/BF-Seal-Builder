#!/usr/bin/env python3
"""Serve the PHP site locally with the same extensionless routes as Apache."""

import argparse
import shutil
import subprocess
from pathlib import Path


ROOT = Path(__file__).resolve().parent.parent
SITE = ROOT / "site"
ROUTER = ROOT / "tools" / "dev-router.php"


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("port", type=int, nargs="?", default=8000)
    parser.add_argument("--bind", default="127.0.0.1")
    args = parser.parse_args()

    php = shutil.which("php")
    if not php:
        raise SystemExit("PHP was not found on PATH. PHP 8.3 or newer is required.")

    address = f"{args.bind}:{args.port}"
    print(f"Serving {SITE} at http://{address}")
    subprocess.run(
        [php, "-S", address, "-t", str(SITE), str(ROUTER)],
        cwd=ROOT,
        check=True,
    )


if __name__ == "__main__":
    main()
