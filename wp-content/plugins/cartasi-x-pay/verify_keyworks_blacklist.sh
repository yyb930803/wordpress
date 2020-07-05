#!/bin/bash
EC=0
if [[ $(grep FIXME . -r | grep -v ^\./verify_keyworks_blacklist\.sh: | wc -l) -ne 0 ]]
then
  echo Found FIXME
  echo
  grep FIXME . -r | grep -v ^\./verify_keyworks_blacklist\.sh:
  echo
  EC=1
fi

if [[ $(grep "error_log" . -r | grep -v ^\./verify_keyworks_blacklist\.sh: | wc -l) -ne 0 ]]
then
  echo Found Error_Log
  echo
  grep "error_log" . -r | grep -v ^\./verify_keyworks_blacklist\.sh:
  echo
  EC=1
fi

if [[ $(grep "console\.log" . -r | grep -v ^\./verify_keyworks_blacklist\.sh: | wc -l) -ne 0 ]]
then
  echo Found console.log
  echo
  grep "console\.log" . -r | grep -v ^\./verify_keyworks_blacklist\.sh:
  echo
  EC=1
fi

exit $EC
