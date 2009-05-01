#!/bin/bash

function get_tmp_dir()
{
#  local tmp_basedir="/tmp/"
#  local tmp_dir="$tmp_basedir/mgt_$(date +%s)"
#  
#  while ! mkdir "$tmp_dir" 2>/dev/null; do
#    tmp_dir="$tmp_basedir/mgt_$(date +%s)"
#  done
#  
#  echo "$tmp_dir"
  mktemp -d -t 'mgt_XXXXXX'
}

function failed()
{
  echo -n "failed" >&2
  [ ! -z "$1" ] && echo ": $1" >&2 || echo "!" >&2
  return 1
  #exit 1
}

function have()
{
  local path
  path="$(which "$1")"
  [ $? = 0 ] || return 1
  [ -x "$path" ] || return 1
  return 0
}

REPO="git://git.tuxfamily.org/gitroot/scengine/website.git"
TEMPDIR="$(get_tmp_dir)" || failed "failed to create temp dir" || exit 1
NAME="bse_git$(date '+%Y%m%d')"
TARNAME="$NAME.tar.gz"

GIT="git"

have "$GIT" || failed "Git not found or not usable (GIT='$GIT')" || exit 1

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
  "$GIT" clone "$REPO" "$NAME" && (
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
) || failed

echo "Removing temp directory $TEMPDIR..."
rm -fr "$TEMPDIR" && echo "done." || failed
