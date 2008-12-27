#!/bin/bash

TMP_PKG_BASE=/tmp/repose-$$
TMP_PKG=${TMP_PKG_BASE}/repose-php

if test "$#" = "2"
then
    if test "$1" = "tag"
    then
        REPOSE_SVN_VERSION=tags/repose-$2
        REPOSE_FILENAME_VERSION=repose-$2
        REPOSE_VISUAL_VERSION="Repose PHP ORM $2"
    else
        REPOSE_SVN_VERSION=branches/repose-$2
        REPOSE_VISUL_VERSION=repose-${2}-snapshot-`date +%Y%m%d%H%M%S`
        REPOSE_VISUAL_VERSION="Repose PHP ORM $2 Snapshot"
    fi
else
    REPOSE_SVN_VERSION=trunk
    REPOSE_FILENAME_VERSION=0.1-snapshot-`date +%Y%m%d%H%M%S`
    REPOSE_VISUAL_VERSION=0.1-snapshot-`date +%Y%m%d%H%M%S`
    REPOSE_VISUAL_VERSION="Repose PHP ORM Trunk Snapshot"
fi

REPOSE_LABELS=Type-Archive,OpSys-All

PACKAGE_BASE=`pwd`/packaged
PACKAGE_NAME_BASE=${PACKAGE_BASE}/repose-php-${REPOSE_FILENAME_VERSION}

mkdir -p ${PACKAGE_BASE} >/dev/null 2>&1

rm -rf ${TMP_PKG_BASE} >/dev/null 2>&1
mkdir -p ${TMP_PKG_BASE} >/dev/null 2>&1

svn export http://repose-php.googlecode.com/svn/${REPOSE_SVN_VERSION}/lib ${TMP_PKG} >/dev/null 2>&1

( cd ${TMP_PKG_BASE}; tar czf ${PACKAGE_NAME_BASE}.tar.gz repose-php/ ) >/dev/null 2>&1
( cd ${TMP_PKG_BASE}; tar cjf ${PACKAGE_NAME_BASE}.tar.bz repose-php/ ) >/dev/null 2>&1
( cd ${TMP_PKG_BASE}; zip ${PACKAGE_NAME_BASE}.zip repose-php/ ) >/dev/null 2>&1


rm -rf ${TMP_PKG_BASE} >/dev/null 2>&1
mkdir -p ${TMP_PKG_BASE} >/dev/null 2>&1

svn export http://repose-php.googlecode.com/svn/${REPOSE_SVN_VERSION} ${TMP_PKG} >/dev/null 2>&1

( cd ${TMP_PKG_BASE}; tar czf ${PACKAGE_NAME_BASE}-full.tar.gz repose-php/ ) >/dev/null 2>&1
( cd ${TMP_PKG_BASE}; tar cjf ${PACKAGE_NAME_BASE}-full.tar.bz repose-php/ ) >/dev/null 2>&1
( cd ${TMP_PKG_BASE}; zip ${PACKAGE_NAME_BASE}-full.zip repose-php/ ) >/dev/null 2>&1

rm -rf ${TMP_PKG_BASE} >/dev/null 2>&1

for i in ${PACKAGE_NAME_BASE}.tar.gz ${PACKAGE_NAME_BASE}.tar.bz ${PACKAGE_NAME_BASE}.zip
do
    echo "googlecode_upload.py -s '${REPOSE_VISUAL_VERSION} (Libraries Only)' -p repose-php -l ${REPOSE_LABELS} $i"
done


for i in ${PACKAGE_NAME_BASE}-full.tar.gz ${PACKAGE_NAME_BASE}-full.tar.bz ${PACKAGE_NAME_BASE}-full.zip
do
    echo "googlecode_upload.py -s '${REPOSE_VISUAL_VERSION} (Full Package)' -p repose-php -l ${REPOSE_LABELS} $i"
done


