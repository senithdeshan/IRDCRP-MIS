<?php

namespace App\Support;

use ZipArchive;

class SimpleXlsxWorkbook
{
    public function write(array $sheets): string
    {
        $path = tempnam(sys_get_temp_dir(), 'irdcrp-xlsx-');
        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            @unlink($path);
            throw new \RuntimeException('Unable to create Excel workbook.');
        }

        $sheetContentTypes = '';
        $sheetEntries = '';
        $sheetRelationships = '';
        $sheetNumber = 0;

        foreach ($sheets as $name => $rows) {
            $sheetNumber++;
            $sheetEntries .= '<sheet name="'.htmlspecialchars((string) $name, ENT_XML1).'" sheetId="'.$sheetNumber.'" r:id="rId'.$sheetNumber.'"/>';
            $sheetRelationships .= '<Relationship Id="rId'.$sheetNumber.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$sheetNumber.'.xml"/>';
            $sheetContentTypes .= '<Override PartName="/xl/worksheets/sheet'.$sheetNumber.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
            $zip->addFromString('xl/worksheets/sheet'.$sheetNumber.'.xml', $this->sheetXml($rows));
        }

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .$sheetContentTypes.'</Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .$sheetRelationships
            .'<Relationship Id="rId'.($sheetNumber + 1).'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets>'.$sheetEntries.'</sheets></workbook>');
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/><color rgb="FFFFFFFF"/></font></fonts>'
            .'<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF0F766E"/><bgColor indexed="64"/></patternFill></fill></fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs>'
            .'</styleSheet>');
        $zip->close();
        $contents = file_get_contents($path);
        @unlink($path);

        if ($contents === false) {
            throw new \RuntimeException('Unable to read generated Excel workbook.');
        }

        return $contents;
    }

    public function read(string $path): array
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            return [];
        }

        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relationshipsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookXml === false || $relationshipsXml === false) {
            $zip->close();
            return [];
        }

        $workbook = simplexml_load_string($workbookXml);
        $relationships = simplexml_load_string($relationshipsXml);

        if ($workbook === false || $relationships === false) {
            $zip->close();
            return [];
        }

        $namespace = $workbook->getNamespaces(true);
        $relationshipNamespace = $namespace['r'] ?? 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
        $targets = [];

        foreach ($relationships->Relationship as $relationship) {
            $targets[(string) $relationship['Id']] = (string) $relationship['Target'];
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $sheets = [];

        foreach ($workbook->sheets->sheet as $sheet) {
            $sheetName = (string) $sheet['name'];
            $relationshipId = (string) $sheet->attributes($relationshipNamespace)['id'];
            $target = $targets[$relationshipId] ?? null;

            if ($target === null) {
                continue;
            }

            $sheetXml = $zip->getFromName($this->worksheetPath($target));
            if ($sheetXml === false) {
                continue;
            }

            $xml = simplexml_load_string($sheetXml);
            if ($xml === false) {
                continue;
            }

            $rows = [];
            foreach ($xml->sheetData->row as $row) {
                $values = [];
                foreach ($row->c as $cell) {
                    $columnIndex = $this->columnIndex((string) $cell['r']);
                    $values[$columnIndex] = $this->cellValue($cell, $sharedStrings);
                }

                if ($values === []) {
                    continue;
                }

                $max = max(array_keys($values));
                $filled = [];
                for ($index = 0; $index <= $max; $index++) {
                    $filled[] = $values[$index] ?? '';
                }
                $rows[(int) $row['r']] = $filled;
            }

            $sheets[$sheetName] = $rows;
        }

        $zip->close();

        return $sheets;
    }

    private function sheetXml(array $rows): string
    {
        $sheetRows = '';
        foreach (array_values($rows) as $rowIndex => $row) {
            $cellXml = '';
            foreach (array_values($row) as $columnIndex => $value) {
                $reference = $this->columnName($columnIndex + 1).($rowIndex + 1);
                $style = $rowIndex === 0 ? ' s="1"' : '';
                $cellXml .= '<c r="'.$reference.'" t="inlineStr"'.$style.'><is><t>'.htmlspecialchars((string) $value, ENT_XML1).'</t></is></c>';
            }
            $sheetRows .= '<row r="'.($rowIndex + 1).'">'.$cellXml.'</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetViews><sheetView workbookViewId="0"/></sheetViews><sheetFormatPr defaultRowHeight="19"/>'
            .'<sheetData>'.$sheetRows.'</sheetData></worksheet>';
    }

    private function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }

        $strings = simplexml_load_string($xml);
        if ($strings === false) {
            return [];
        }

        $result = [];
        foreach ($strings->si as $item) {
            $text = isset($item->t) ? (string) $item->t : '';
            foreach ($item->r as $run) {
                $text .= (string) $run->t;
            }
            $result[] = trim($text);
        }

        return $result;
    }

    private function cellValue(\SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) $cell['t'];
        if ($type === 'inlineStr') {
            $text = isset($cell->is->t) ? (string) $cell->is->t : '';
            foreach ($cell->is->r as $run) {
                $text .= (string) $run->t;
            }
            return trim($text);
        }

        $value = (string) $cell->v;
        if ($type === 's') {
            return $sharedStrings[(int) $value] ?? '';
        }
        if ($type === 'b') {
            return $value === '1' ? 'TRUE' : 'FALSE';
        }

        return trim($value);
    }

    private function worksheetPath(string $target): string
    {
        $target = str_replace('\\', '/', $target);
        if (str_starts_with($target, '/')) {
            return ltrim($target, '/');
        }
        return str_starts_with($target, 'xl/') ? $target : 'xl/'.$target;
    }

    private function columnIndex(string $reference): int
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($reference));
        $index = 0;
        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
    }

    private function columnName(int $index): string
    {
        $name = '';
        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)).$name;
            $index = intdiv($index, 26);
        }

        return $name;
    }
}
