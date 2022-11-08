<?php

namespace CustomPaymentGateway\Cron;

interface CronInterface {
	public static function cronJob();
}