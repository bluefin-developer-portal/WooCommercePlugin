REPO_VERSION=`node -e "console.log(require('./package.json').version)"` && echo TAG: v$REPO_VERSION && git commit -a -m v$REPO_VERSION && git push && git tag v$REPO_VERSION && git push --tags;
