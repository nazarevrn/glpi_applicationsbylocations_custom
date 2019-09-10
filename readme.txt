Доработка GLPI для генерации отчётов об установленном ПО.

Зависимости (не входящие в стандартный пакет поставки GLPI)

1) jquery            ver 3.3.1
2) select2           ver 4.0.7
3) jquery.dataTables ver 1.10.19
4) clipboard.js      ver 2.0.0

Возможно, мне в будущем будет за это стыдно, но сейчас все зависимости тупо лежат в соответствующих папках js и css.
Зато РКН ничего не сломает...
То есть, для работы этой поделки, достаточно скопировать содержимое этой репы
на любой развернутый GLPI сервер в папку plugins/reports/report/applicationsbylocations_custom. 
Затем в веб-интерфейсе GLPI найти отчёт applicationsbylocations_custom. (я не заморачивался с локализацией)
Но это неточно.

Важно!
название файла applicationsbylocations_custom.php должно совпадать с названием директории, в которой он лежит

Ну и репа на всякий случай https://github.com/nazarevrn/glpi_applicationsbylocations_custom
