#!/usr/bin/env bash

# Ansi color code variables
RED="\e[0;91m"
BLUE="\e[0;94m"
EXPAND_BG="\e[K"
BLUE_BG="\e[0;104m${expand_bg}"
RED_BG="\e[0;101m${expand_bg}"
GREEN_BG="\e[0;102m${expand_bg}"
GREEN="\e[0;92m"
WHITE="\e[0;97m"
BOLD="\e[1m"
ULINE="\e[4m"
RESET="\e[0m"

USER_NAME=waglpz
IMAGE_NAME=${USER_NAME}/$(basename $PWD)

if  [[ $(docker ps -q) != "" ]]; then
    echo
    echo
    echo -e "${RED}INFO:${RESET} ${GREEN}Docker Services now active (Up)${RESET}"
    echo
    docker ps
    docker ps -aq | xargs docker stop
    docker ps -aq | xargs docker rm -f
    docker network prune -f
    echo
    echo
    echo -e "${RED}INFO:${RESET} ${GREEN}Docker Services shuting down.${RESET}"
fi 

echo -e "${GREEN}Begin create Docker image '${IMAGE_NAME}' ...${RESET}"

docker build                        \
       --no-cache                   \
       --force-rm                   \
       --tag ${IMAGE_NAME}          \
       --build-arg APPUID=$(id -u)  \
       --build-arg APPUGID=$(id -g) \
.docker

docker images | grep "$(basename $PWD)"

echo -e "${GREEN}build image ${IMAGE_NAME} done.${RESET}"

echo -e "${GREEN}Run ${IMAGE_NAME} ...${RESET}"

docker run                \
      --user ${USER_NAME} \
      --rm -ti            \
      -v $PWD:/app        \
      -v $PWD/.docker/    \
      -v $PWD/.docker/php/php-ini-overrides.ini:/usr/local/etc/php/conf.d/99-overrides.ini \
${IMAGE_NAME} bash
