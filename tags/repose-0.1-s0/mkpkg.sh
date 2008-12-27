#!/bin/bash

TMP_PKG_BASE=/tmp/repose-$$
TMP_PKG=${TMP_PKG_BASE}/repose-php

if test "$#" -eq "1"
then
    REPOSE_SVN_VERSION=$1
    REPOSE_VISUAL_VERSION=0.1
else
    REPOSE_SVN_VERSION=trunk
    REPOSE_VISUAL_VERSION=0.1-snapshot-`date +%Y%m%d%H%M%S`
fi

PACKAGE_BASE=`pwd`/packaged
PACKAGE_NAME_BASE=${PACKAGE_BASE}/repose-php-${REPOSE_VISUAL_VERSION}
mkdir -p ${PACKAGE_BASE}

rm -rf ${TMP_PKG_BASE}
mkdir -p ${TMP_PKG_BASE}

svn export http://repose-php.googlecode.com/svn/${REPOSE_SVN_VERSION}/lib ${TMP_PKG}

( cd ${TMP_PKG_BASE}; tar czf ${PACKAGE_NAME_BASE}.tar.gz repose-php/ )
( cd ${TMP_PKG_BASE}; tar cjf ${PACKAGE_NAME_BASE}.tar.bz repose-php/ )
( cd ${TMP_PKG_BASE}; zip ${PACKAGE_NAME_BASE}.zip repose-php/ )


rm -rf ${TMP_PKG_BASE}
mkdir -p ${TMP_PKG_BASE}

echo svn export http://repose-php.googlecode.com/svn/${REPOSE_SVN_VERSION} ${TMP_PKG}
svn export http://repose-php.googlecode.com/svn/${REPOSE_SVN_VERSION} ${TMP_PKG}

( cd ${TMP_PKG_BASE}; tar czf ${PACKAGE_NAME_BASE}-full.tar.gz repose-php/ )
( cd ${TMP_PKG_BASE}; tar cjf ${PACKAGE_NAME_BASE}-full.tar.bz repose-php/ )
( cd ${TMP_PKG_BASE}; zip ${PACKAGE_NAME_BASE}-full.zip repose-php/ )

rm -rf ${TMP_PKG_BASE}

echo ${TMP_PKG_BASE}
