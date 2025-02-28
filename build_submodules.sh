#!/bin/bash
set -e

cd pecl-dbus
phpize
./configure
make -j$(nproc)
sudo make install