#!/bin/sh

# Authorize SSH access from host
if [ -f ~/.ssh/host_key.pub ]; then
    touch ~/.ssh/authorized_keys
    chmod 644 ~/.ssh/authorized_keys
    cat ~/.ssh/host_key.pub >> ~/.ssh/authorized_keys
fi
