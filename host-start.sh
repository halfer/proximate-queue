# Docker command to launch Proximate queue
#
# Since this is a standalone Docker launcher, localhost is used - in Docker Compose
# the server name of the proxy server would be used.

# Get the FQ path of this project
STARTDIR=`pwd`
cd `dirname $0`
ROOTDIR=`pwd`

docker run \
    -v ${ROOTDIR}/queue:/var/proximate/queue \
    -e PROXY_ADDRESS="localhost:8081" \
    -t \
    proximate-queue

# Go back to original dir
cd $STARTDIR
