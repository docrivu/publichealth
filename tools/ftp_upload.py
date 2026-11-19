"""Upload selected files from the repo's public_html/ to the live server over FTP(S).

Environment:
  FTP_HOST, FTP_PORT (21), FTP_USER, FTP_PASSWORD (prompted if missing)
  FTP_SECURE        "true" (default) = FTPS with explicit TLS; "false" = plain FTP
  FTP_REMOTE_ROOT   folder to enter after login (default "public_html"; use "." if
                    the FTP account already lands inside public_html)
  FTP_LOCAL_ROOT    local folder (default "public_html")
  FTP_FILES         files to upload, relative to FTP_LOCAL_ROOT (":" or newline separated)
  FTP_DIRS          whole folders to upload (":" or newline separated)
  FTP_DELETE_FILES  remote files to delete (":" or newline separated)
"""
from __future__ import annotations

import getpass
import os
import re
from ftplib import FTP, FTP_TLS
from pathlib import Path


def env_list(name: str) -> list[str]:
    return [item.strip() for item in re.split(r"[:\n]+", os.environ.get(name, "")) if item.strip()]


HOST = os.environ.get("FTP_HOST", "145.79.213.48")
PORT = int(os.environ.get("FTP_PORT") or "21")
USER = os.environ["FTP_USER"]
SECURE = os.environ.get("FTP_SECURE", "true").lower() != "false"
REMOTE_ROOT = os.environ.get("FTP_REMOTE_ROOT") or "public_html"
LOCAL_ROOT = Path(os.environ.get("FTP_LOCAL_ROOT", "public_html")).resolve()
FILES = env_list("FTP_FILES")
DIRS = env_list("FTP_DIRS")
DELETE_FILES = env_list("FTP_DELETE_FILES")

# Never push secrets or server-only state, even if they end up in the repo by mistake.
BLOCKED = re.compile(r"(^|/)(config/app\.php|\.env|storage/|\.git/|\.DS_Store$)")


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
    if not (FILES or DIRS or DELETE_FILES):
        print("Nothing to upload.")
        return

    password = os.environ.get("FTP_PASSWORD") or getpass.getpass("FTP password: ")
    ftp = FTP_TLS() if SECURE else FTP()
    with ftp:
        ftp.connect(HOST, PORT, timeout=30)
        ftp.login(USER, password)
        if SECURE:
            ftp.prot_p()  # encrypt file transfers, not just the login
        if REMOTE_ROOT not in {"", "."}:
            ftp.cwd(REMOTE_ROOT)  # fails loudly if the folder is wrong
        print(f"connected to {HOST}, remote folder: {ftp.pwd()}")

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
    remote_path = item.replace("\\", "/")
    if BLOCKED.search(remote_path):
        print(f"skip (protected) {remote_path}")
        return

    local_path = (LOCAL_ROOT / item).resolve()
    if not local_path.is_file() or LOCAL_ROOT not in local_path.parents:
        raise FileNotFoundError(local_path)

    ensure_remote_dir(ftp, str(Path(remote_path).parent))
    print(f"upload {remote_path}")
    with local_path.open("rb") as handle:
        ftp.storbinary(f"STOR {remote_path}", handle)


if __name__ == "__main__":
    main()
