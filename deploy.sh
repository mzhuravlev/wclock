#!/bin/sh
php app/console --env=prod cache:clear
php app/console --env=prod assetic:dump
php app/console --env=prod cache:warmup