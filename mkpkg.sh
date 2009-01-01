#!/bin/bash

TMP_PKG_BASE=/tmp/repose-$$

if ! test -e .googlecodeauth
then
    echo "Google Code auth information required."
    echo -n 'Username: ';
    read USERNAME
    echo -n 'Password: ';
    read PASSWORD
    echo "$USERNAME:$PASSWORD" > .googlecodeauth
    chmod 600 .googlecodeauth
fi

if test "$#" = "2"
then
    if test "$1" = "tag"
    then
        REPOSE_SVN_VERSION=tags/repose-$2
        REPOSE_FILENAME_VERSION=repose-$2
        REPOSE_VISUAL_VERSION="Repose PHP ORM $2"
    else
        REPOSE_SVN_VERSION=branches/repose-$2
        REPOSE_FILENAME_VERSION=repose-${2}-snapshot-`date +%Y%m%d%H%M%S`
        REPOSE_VISUAL_VERSION="Repose PHP ORM $2 Snapshot"
    fi
else
    REPOSE_SVN_VERSION=trunk
    REPOSE_FILENAME_VERSION=repose-snapshot-`date +%Y%m%d%H%M%S`
    REPOSE_VISUAL_VERSION="Repose PHP ORM Trunk Snapshot"
fi

TMP_PKG=${TMP_PKG_BASE}/${REPOSE_FILENAME_VERSION}

REPOSE_LABELS=Type-Archive,OpSys-All

PACKAGE_BASE=`pwd`/packaged
PACKAGE_NAME_BASE=${PACKAGE_BASE}/${REPOSE_FILENAME_VERSION}

mkdir -p ${PACKAGE_BASE} >/dev/null 2>&1

rm -rf ${TMP_PKG_BASE} >/dev/null 2>&1
mkdir -p ${TMP_PKG_BASE} >/dev/null 2>&1

svn export http://repose-php.googlecode.com/svn/${REPOSE_SVN_VERSION}/lib ${TMP_PKG} >/dev/null 2>&1

( cd ${TMP_PKG_BASE}; tar czf ${PACKAGE_NAME_BASE}.tar.gz ${REPOSE_FILENAME_VERSION} ) >/dev/null 2>&1
( cd ${TMP_PKG_BASE}; tar cjf ${PACKAGE_NAME_BASE}.tar.bz ${REPOSE_FILENAME_VERSION} ) >/dev/null 2>&1
( cd ${TMP_PKG_BASE}; zip ${PACKAGE_NAME_BASE}.zip ${REPOSE_FILENAME_VERSION} ) >/dev/null 2>&1


rm -rf ${TMP_PKG_BASE} >/dev/null 2>&1
mkdir -p ${TMP_PKG_BASE} >/dev/null 2>&1

svn export http://repose-php.googlecode.com/svn/${REPOSE_SVN_VERSION} ${TMP_PKG} >/dev/null 2>&1

( cd ${TMP_PKG_BASE}; tar czf ${PACKAGE_NAME_BASE}-full.tar.gz ${REPOSE_FILENAME_VERSION} ) >/dev/null 2>&1
( cd ${TMP_PKG_BASE}; tar cjf ${PACKAGE_NAME_BASE}-full.tar.bz ${REPOSE_FILENAME_VERSION} ) >/dev/null 2>&1
( cd ${TMP_PKG_BASE}; zip ${PACKAGE_NAME_BASE}-full.zip ${REPOSE_FILENAME_VERSION} ) >/dev/null 2>&1

rm -rf ${TMP_PKG_BASE} >/dev/null 2>&1

for i in ${PACKAGE_NAME_BASE}.tar.gz ${PACKAGE_NAME_BASE}.zip
do
    ./googlecode_upload.php repose-php $i '${REPOSE_VISUAL_VERSION} (Libraries Only)' '${REPOSE_LABELS}'
done


for i in ${PACKAGE_NAME_BASE}-full.tar.gz ${PACKAGE_NAME_BASE}-full.zip
do
    ./googlecode_upload.php repose-php $i '${REPOSE_VISUAL_VERSION} (Full Package)' '${REPOSE_LABELS}'
done


