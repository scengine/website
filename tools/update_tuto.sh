#!/bin/bash

#~ set -x

CWD="$(dirname $0)/"

PHP_OUTPUT="$CWD/../tuto.php"
MDOWN_INPUT=$1
HEADER="$CWD/tpl/tuto_header.php"
FOOTER="$CWD/tpl/tuto_footer.php"

MDOWN=mdown

if [ -z "$MDOWN_INPUT" -o "$1" = "--help" -o "$1" = "-h" ]; then
  echo "Usage: $0 mdownFile" >&2
  exit 1
elif [ ! -f "$MDOWN_INPUT" ]; then
  echo "Input is not a regular file" >&2
  exit 1
fi

TEMPFILE=`tempfile`
#~ "$MDOWN" -f xhtml -H "$HEADER" -F "$FOOTER" "$MDOWN_INPUT" > "$TEMPFILE"
"$MDOWN" -f xhtml "$MDOWN_INPUT" > "$TEMPFILE"
if [ $? != 0 ]; then
  echo "mdown error" >&2
  exit 1
fi

#============ start haking for a good page ==============#

# first, extract header to put it as presentation
sed -i 's/\(<h[2-6]>\)/\n\n\1/g' "$TEMPFILE" # we add a empty line before ant titles

PRESENTATION=`tempfile`
NLINES=`tempfile`
echo -n 0 > "$NLINES"

cat "$TEMPFILE" | while read line; do
  #~ echo $line
  nl=$(cat "$NLINES")
  ((nl++))
  echo -n $nl > "$NLINES"
  
  [ -z "$line" ] && break
  echo -n "$line " | sed 's/\([\&|]\)/\\\1/g' >> "$PRESENTATION"
done

sed -i 's|<h1>|<h2>|g' "$PRESENTATION"
sed -i 's|</h1>|</h2>|g' "$PRESENTATION"

TEMPH=`tempfile`

sed -e 's|${PRESENTATION}|'"$(cat "$PRESENTATION")"'|' "$HEADER" > "$TEMPH"

PRESLENLINES=$(cat "$NLINES")

rm -f "$PRESENTATION"
rm -f "$NLINES"


# then processe rest of tuto

if [ ! -z $(grep '</\?h[56]>' "$TEMPFILE") ]; then
  echo 'WW: there is H6 titles, they will be replaced by <div class="h8">.' >&2
  
fi

sed -i 's|<h6>|<div class="h7">|g' "$TEMPFILE"
sed -i 's|</h6>|</div>|g' "$TEMPFILE"
sed -i 's|<h5>|<h6>|g' "$TEMPFILE"
sed -i 's|</h5>|</h6>|g' "$TEMPFILE"
sed -i 's|<h4>|<h5>|g' "$TEMPFILE"
sed -i 's|</h4>|</h5>|g' "$TEMPFILE"
sed -i 's|<h3>|<h4>|g' "$TEMPFILE"
sed -i 's|</h3>|</h4>|g' "$TEMPFILE"
sed -i 's|<h2>|<h3>|g' "$TEMPFILE"
sed -i 's|</h2>|</h3>|g' "$TEMPFILE"
sed -i 's|<h1>|<h2>|g' "$TEMPFILE"
sed -i 's|</h1>|</h2>|g' "$TEMPFILE"

TEMPFILE2=`tempfile`

#~ cat "$HEADER" >> "$TEMPFILE2"
cat "$TEMPH" >> "$TEMPFILE2"
tail -n +$PRESLENLINES "$TEMPFILE" >> "$TEMPFILE2" # copy tuto without the intro
cat "$FOOTER" >> "$TEMPFILE2"

install -m 644 -g www-data "$TEMPFILE2" "$PHP_OUTPUT"

rm -f "$TEMPFILE"
rm -f "$TEMPFILE2"
rm -f "$TEMPH"
