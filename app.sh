#!/bin/bash
service apache2 start
while(true); do
  service apache2 status
  sleep 1
done
