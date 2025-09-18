@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../dereuromark/media-embed/bin/generate-docs
SET COMPOSER_RUNTIME_BIN_DIR=%~dp0
sh "%BIN_TARGET%" %*
