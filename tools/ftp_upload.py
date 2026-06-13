from __future__ import annotations

import getpass
import os
from ftplib import FTP
from pathlib import Path


HOST = os.environ.get("FTP_HOST", "145.79.213.48")
PORT = int(os.environ.get("FTP_PORT", "21"))
USER = os.environ["FTP_USER"]
LOCAL_ROOT = Path(os.environ.get("FTP_LOCAL_ROOT", "public_html")).resolve()
FILES = [item for item in os.environ.get("FTP_FILES", "").split(":") if item]
DIRS = [item for item in os.environ.get("FTP_DIRS", "").split(":") if item]
DELETE_FILES = [item for item in os.environ.get("FTP_DELETE_FILES", "").split(":") if item]


def ensure_remote_dir(ftp: FTP, directory: str) -> None:
    if directory in {"", "."}:
        return

    start = ftp.pwd()
    for segment in directory.strip("/").split("/"):
        if not segment:
            continue
        try:
            ftp.cwd(segment)
        except Exception:
            ftp.mkd(segment)
            ftp.cwd(segment)
    ftp.cwd(start)


def main() -> None:
    password = os.environ.get("FTP_PASSWORD") or getpass.getpass("FTP password: ")
    with FTP() as ftp:
        ftp.connect(HOST, PORT, timeout=30)
        ftp.login(USER, password)

        for item in FILES:
            upload_file(ftp, item)

        for directory in DIRS:
            for local_path in sorted((LOCAL_ROOT / directory).rglob("*")):
                if local_path.is_file():
                    upload_file(ftp, str(local_path.relative_to(LOCAL_ROOT)))

        for item in DELETE_FILES:
            try:
                print(f"delete {item}")
                ftp.delete(item)
            except Exception as exc:
                print(f"skip delete {item}: {exc}")


def upload_file(ftp: FTP, item: str) -> None:
    local_path = (LOCAL_ROOT / item).resolve()
    if not local_path.is_file() or LOCAL_ROOT not in local_path.parents:
        raise FileNotFoundError(local_path)

    remote_path = item.replace("\\", "/")
    ensure_remote_dir(ftp, str(Path(remote_path).parent))
    print(f"upload {remote_path}")
    with local_path.open("rb") as handle:
        ftp.storbinary(f"STOR {remote_path}", handle)


if __name__ == "__main__":
    main()
