#!/bin/sh

#
#  STEP 1:
#  extract all Stud.IP message strings and merge them with the existing translations
#

LOCALE_RELATIVE_PATH="locale"
TRANSLATIONFILES_RELATIVE_PATHS="."

for language in en
do
    test -f "$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/tracer.po" && mv "$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/tracer.po" "$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/tracer.po.old"
    > "$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/tracer.UTF-8.po"
    find $TRANSLATIONFILES_RELATIVE_PATHS \( -iname "*.php" \) | xargs xgettext --from-code=ISO-8859-1 -j -n --language=PHP -o "$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/tracer.UTF-8.po"
    msgconv --to-code=iso-8859-1 "$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/tracer.UTF-8.po" -o "$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/tracer.po"
    test -f "$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/tracer.po.old" && msgmerge "$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/tracer.po.old" "$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/tracer.po" --output-file="$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/tracer.po"
    test -f "$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/tracer.UTF-8.po" && rm "$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/tracer.UTF-8.po"
done
