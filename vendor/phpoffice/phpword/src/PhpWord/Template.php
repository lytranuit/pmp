<?php

/**
 * This file is part of PHPWord - A pure PHP library for reading and writing
 * word processing documents.
 *
 * PHPWord is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPWord/contributors.
 *
 * @see         https://github.com/PHPOffice/PHPWord
 * @copyright   2010-2018 PHPWord contributors
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

namespace PhpOffice\PhpWord;

/**
 * @deprecated 0.12.0 Use `\PhpOffice\PhpWord\TemplateProcessor` instead.
 *
 * @codeCoverageIgnore
 */
class Template extends TemplateProcessor
{
    public function cloneTable($search, $numberOfClones)
    {
        if (substr($search, 0, 2) !== '${' && substr($search, -1) !== '}') {
            $search = '${' . $search . '}';
        }

        $tagPos = strpos($this->documentXML, $search);
        // if (!$tagPos) {
        //     throw new Exception("Can not clone table, template variable not found or variable contains markup.");
        // }

        $tableStart = $this->findTableStart($tagPos);
        $tableEnd = $this->findTableEnd($tagPos);

        // nested table logic: find correct end tag position 
        $openings = 0;
        $tableStartNext = $this->findTableStart($tableStart + 1, true);
        while ($tableStartNext !== false && $tableStartNext < $tableEnd) {
            $openings++;
            $tableStartNext = $this->findTableStart($tableStartNext + 1, true);
            while ($openings > 0 &&  ($tableStartNext === false || $tableEnd < $tableStartNext)) {
                $openings--;
                $tableEnd = $this->findTableEnd($tableEnd + 1);
            }
        } // /nested table logic end

        $xmlRow = $this->getSlice($tableStart, $tableEnd);

        $result = $this->getSlice(0, $tableStart);
        for ($i = 1; $i <= $numberOfClones; $i++) {
            $result .= preg_replace('/\$\{(.*?)\}/', '\${\\1#' . $i . '}', $xmlRow);
        }
        $result .= $this->getSlice($tableEnd);

        $this->documentXML = $result;
    }

    private function findTableStart($offset, $forward = false)
    {
        // beware: strpos != strrpos
        if ($forward) {
            $tableStart = strpos($this->documentXML, "<w:tbl ", $offset);
            if (!$tableStart) {
                $tableStart = strpos($this->documentXML, "<w:tbl>", $offset);
            }
        } else {
            $tableStart = strrpos($this->documentXML, "<w:tbl ", ((strlen($this->documentXML) - $offset) * -1));
            if (!$tableStart) {
                $tableStart = strrpos($this->documentXML, "<w:tbl>", ((strlen($this->documentXML) - $offset) * -1));
            }
            // if (!$tableStart) {
            //     throw new Exception("Can not find the start position of the row to clone.");
            // }
        }
        return $tableStart;
    }

    private function findTableEnd($offset)
    {
        $tableEnd = strpos($this->documentXML, "</w:tbl>", $offset) + 8;
        return $tableEnd;
    }
}
