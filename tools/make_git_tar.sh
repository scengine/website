#!/bin/bash

function get_tmp_dir()
{
  local tmp_basedir="/tmp/"
  local tmp_dir="$tmp_basedir/mgt_$(date +%s)"
  
  while ! mkdir "$tmp_dir" 2>/dev/null; do
    tmp_dir="$tmp_basedir/mgt_$(date +%s)"
  done
  
  echo "$tmp_dir"
}

function failed()
{
  echo "failed!" >&2
  #exit 1
}
REPO="git://git.tuxfamily.org/gitroot/scengine/website.git"
TEMPDIR="$(get_tmp_dir)"
NAME="bse_git$(date '+%Y%m%d')"
TARNAME="$NAME.tar.gz"

if [ $# -gt 0 ]; then
  OUT="$1"
  if [ ${OUT:0:1} != '/' ]; then
    OUT="$PWD/$OUT"
  fi
else
  OUT="/tmp/$TARNAME"
fi

cd "$TEMPDIR" && (
  echo "Fetching repository..."
  git clone "$REPO" "$NAME" && (
    echo "done."
    echo "Creating tar..."
    tar -czv --exclude '.git' -f "$TARNAME" "$NAME" && (
      echo "done."
      echo "Moving '$TARNAME' to '$OUT'..."
      mv -i "$TARNAME" "$OUT" \
        && echo "done." \
        || failed
    ) || failed
  ) || failed
)

echo "Removing temp directory $TEMPDIR..."
rm -fr "$TEMPDIR" && echo "done." || failed
