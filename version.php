<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package qtype_scmc
 * @author ETH Zurich (moodle@id.ethz.ch)
 * @copyright ETHz 2016
 */
defined('MOODLE_INTERNAL') || die();

$plugin->component = 'qtype_scmc';
$plugin->version = 2025052100;
$plugin->requires = 2021050100; // Moodle >=3.11+.

$plugin->maturity = MATURITY_STABLE;
$plugin->release = '5.0';
