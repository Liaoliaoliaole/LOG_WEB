<?php
/*
File: morfeas_SDAQnet_proxy.php PHP Script for the Morfeas_Web. Part of Morfeas_project.
Copyright (C) 12019-12021  Sam harry Tzavaras

	This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
require("./Supplementary.php");
ob_start("ob_gzhandler");//Enable gzip buffering
//Disable caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: report/text');

$requestType = $_SERVER['REQUEST_METHOD'];
if($requestType == "GET")
{
	if(array_key_exists("SDAQnet", $_GET)&&array_key_exists("SDAQaddr", $_GET))
	{
		$SDAQ_net=$_GET['SDAQnet'];
		$SDAQ_addr=$_GET['SDAQaddr'];
		exec("SDAQ_worker $SDAQ_net getinfo $SDAQ_addr -s 2>&1", $output, $retval);
		if(!$retval)
		{
			header('Content-Type: Morfeas_SDAQ_calibration_data/xml');
			die(implode("\n",$output));
		}
		else
			die("Error: $output[0]");
	}
	else if(array_key_exists("UNITs", $_GET))
	{
		exec("SDAQ_psim -u 2>&1", $output, $retval);
		if(!$retval)
		{
			header('Content-Type: Morfeas_SDAQ_units/json');
			die(implode("\n",$output));
		}
		else
			die("Error: $output[0]");
	}
}
else if($requestType == "POST")
{
	if(($SDAQ_cal_data = file_get_contents('php://input')))
	{
		$SDAQ_cal_data = decompress($SDAQ_cal_data) or die("Error: Decompressing of SDAQ's Calibration data!!!");
		$SDAQ_cal_data = json_decode($SDAQ_cal_data) or die("Error: JSON_Decode of SDAQ's Calibration data!!!");
		if(property_exists($SDAQ_cal_data, 'SDAQnet')&&property_exists($SDAQ_cal_data, 'SDAQaddr'))
		{
			$SDAQ_net=$SDAQ_cal_data->SDAQnet;
			$SDAQ_addr=$SDAQ_cal_data->SDAQaddr;
			if(property_exists($SDAQ_cal_data, 'XMLcontent'))
			{
				$SDAQ_xml_data=$SDAQ_cal_data->XMLcontent;
				exec("echo '$SDAQ_xml_data' | SDAQ_worker $SDAQ_net setinfo $SDAQ_addr -vsf-.xml 2>&1", $output, $retval);
				if(!$retval)
					die("Server: Calibration table written with success at SDAQ with ADDR:$SDAQ_addr");
				else
					die(implode("\n",$output));
			}
			else if(property_exists($SDAQ_cal_data, 'SDAQ_firmware_HEX'))
			{
				$SDAQ_firmware_HEX=$SDAQ_cal_data->SDAQ_firmware_HEX;
				exec("echo '$SDAQ_firmware_HEX' | SDAQ_prog $SDAQ_net $SDAQ_addr -si 2>&1", $output, $retval);
				if(!$retval)
					die("Server: Success firmware update for SDAQ with ADDR:$SDAQ_addr");
				else
					die(implode("\n",$output));
			}
		}
	}
}
http_response_code(404);
?>
