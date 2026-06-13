from __future__ import annotations

import getpass
import os
from ftplib import FTP
from pathlib import Path


HOST = os.environ.get("FTP_HOST", "145.79.213.48")
PORT = int(os.environ.get("FTP_PORT", "21"))
USER = os.environ["FTP_USER"]
REMOTE_ROOT = os.environ.get("FTP_REMOTE_ROOT", "public_html")
LOCAL_ROOT = Path(os.environ.get("FTP_LOCAL_ROOT", "public_html")).resolve()


def is_dir(ftp: FTP, name: str) -> bool:
    current = ftp.pwd()
    try:
        ftp.cwd(name)
        ftp.cwd(current)
        return True
    except Exception:
        try:
            ftp.cwd(current)
        except Exception:
            pass
        return False


def download_dir(ftp: FTP, remote: str, local: Path) -> None:
    local.mkdir(parents=True, exist_ok=True)
    ftp.cwd(remote)
    for name in ftp.nlst():
        if name in {".", ".."}:
            continue
        target = local / name
        if is_dir(ftp, name):
            download_dir(ftp, name, target)
            ftp.cwd("..")
        else:
            print(f"download {target.relative_to(LOCAL_ROOT.parent)}")
            with target.open("wb") as handle:
                ftp.retrbinary(f"RETR {name}", handle.write)


def main() -> None:
    password = os.environ.get("FTP_PASSWORD") or getpass.getpass("FTP password: ")
    with FTP() as ftp:
        ftp.connect(HOST, PORT, timeout=30)
        ftp.login(USER, password)
        if os.environ.get("FTP_LIST_ONLY") == "1":
            print(f"cwd {ftp.pwd()}")
            for name in ftp.nlst():
                print(name)
            return
        download_dir(ftp, REMOTE_ROOT, LOCAL_ROOT)


if __name__ == "__main__":
    main()
