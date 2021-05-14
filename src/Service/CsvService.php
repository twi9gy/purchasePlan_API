<?php

namespace App\Service;

use DateTime;
use SplFileInfo;

class CsvService
{
    private $csvFile;
    private $salesFiles;
    private $basePath;
    private $column;

    /**
     * @param string $csv_file  - путь до csv-файла
     */
    public function __construct($csv_file, $salesFiles, $basePath, $column)
    {
        $this->csvFile = $csv_file;
        $this->salesFiles = $salesFiles;
        $this->basePath = $basePath;
        $this->column = $column;
    }

    public function setCSV(string $filename, Array $csv, string $separator): void
    {
        //Открываем csv для до-записи,
        $handle = fopen($filename, 'ab');

        foreach ($csv as $value) {
            //Записываем, 3-ий параметр - разделитель поля
            fputcsv($handle, explode($separator, $value), $separator);
        }

        //Закрываем файл
        fclose($handle);
    }

    /**
     * Метод для чтения из csv-файла. Возвращает массив с данными из csv
     * @param string $filename
     * @param string $separator
     * @return array;
     */
    public function getCSV(string $filename, string $separator): array
    {
        //Открываем csv для чтения
        $handle = fopen($filename, 'rb');

        //Массив будет хранить данные из csv
        $array_line_full = array();
        //Проходим весь csv-файл, и читаем построчно. 3-ий параметр разделитель поля
        while (($line = fgetcsv($handle, 0, $separator)) !== false) {
            $array_line_full[] = $line;
        }

        //Закрываем файл
        fclose($handle);
        return $array_line_full;
    }

    public function isTime($time): bool
    {
        try {
            if (preg_match("/^\d{2,4}[^\d]\d{2,4}[^\d]\d{2,4}$/", $time)) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function aggregationSalesFiles(): void
    {
        $salesFilesCsv = [];
        $aggregationDemand = [];
        $result = [];

        // Поиск файлов с расширением  '.csv'
        foreach ($this->salesFiles as $salesFile) {
            $info = new SplFileInfo($salesFile->getFilename());
            if ($info->getExtension() === 'csv' && !$salesFile->getCreatedByCategory()) {
                $fileContent = $this->getCSV(
                    $this->basePath . '/' .$salesFile->getFilename(),
                    $salesFile->getSeparator()
                );

                // Приводим дату из файла к единому формату
                foreach ($fileContent as $i => $iValue) {
                    if ($this->isTime($iValue[0])) {
                        $fileContent[$i][0] = DateTime::createFromFormat(
                            'd.m.y',
                            $iValue[0]
                        )->format('Y.m.d');
                    }
                }

                $salesFilesCsv[] = $fileContent;
            }
        }

        if (count($salesFilesCsv) > 0) {
            $oldestDate = (new \DateTime())->format('Y.m.d');
            $newestDate = (new \DateTime())->modify('- 10 year')->format('Y.m.d');

            // Поиск самой старой даты и самой новой даты
            foreach ($salesFilesCsv as $salesFile) {
                foreach ($salesFile as $item) {
                    if (isset($item[0]) && $this->isTime($item[0]) && $item[0] < $oldestDate) {
                        $oldestDate = $item[0];
                        break;
                    }
                }

                $lastElem = end($salesFile)[0];
                if ($lastElem && $lastElem > $newestDate) {
                    $newestDate = $lastElem;
                }
            }

            $oldestDate = DateTime::createFromFormat(
                'Y.m.d',
                $oldestDate
            );

            $newestDate = DateTime::createFromFormat(
                'Y.m.d',
                $newestDate
            );

            $period = new \DatePeriod($oldestDate, new \DateInterval('P1D'), $newestDate);
            $arrayOfDates = array_map(
                static function($item){return $item->format('Y.m.d');},
                iterator_to_array($period)
            );

            if (count($arrayOfDates) > 0) {
                // объединение данных из файлов
                // Цикл по диапозону дат
                foreach ($arrayOfDates as $arrayOfDate) {
                    $aggregationDemand[$arrayOfDate] = [
                        'Date' => $arrayOfDate,
                        (string)$this->column => 0
                    ];
                    // цикл по файлам
                    foreach ($salesFilesCsv as $salesFile) {
                        // цикл по данным из файлов
                        foreach ($salesFile as $elem) {
                            if (isset($elem[0]) && $this->isTime($elem[0])) {
                                if ($elem[0] === $arrayOfDate) {
                                    $aggregationDemand[$arrayOfDate][(string)$this->column] += (int)$elem[1];
                                }

                                if ($elem[0] > $arrayOfDate) {
                                    break;
                                }
                            }
                        }
                    }
                }

                // Формируем записи для нового csv файла.
                $result[] = 'Date;' . $this->column;
                foreach ($aggregationDemand as $demand) {
                    $result[] = $demand['Date'] . ';' . $demand[(string)$this->column];
                }

                $this->setCSV($this->csvFile, $result, ';');
            }
        }
    }
}
