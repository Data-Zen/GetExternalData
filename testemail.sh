#!/bin/bash

if test $# -ne 2; then
   echo "USAGE: $0: \"email address of recipient\" \"message subject\""
   exit;
fi
(echo "To: $1
From: Your_email_address
Subject: $2

This is a test message
") | sendmail -oi -t