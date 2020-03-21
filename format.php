<?php

function P5($value)
{
	return str_pad($value,5);
}
function R($value)
{
	return "\e[91m".$value."\e[0m";
}	
function Y($value)
{
	return "\e[93m".$value."\e[0m";
}
function G($value)
{
	return "\e[92m".$value."\e[0m";
}
function C($value)
{
	return "\e[96m".$value."\e[0m";
}
function TITLE($value)
{
	return "\e[104m".$value."\e[0m";
}