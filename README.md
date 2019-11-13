# Установка
Перейти в **DOCUMENT_ROOT**

Выполнить:
```
composer require yngc0der/bitrix-phpcondition:dev-master
```
и
```
composer run-script post-install-cmd -d bitrix/modules/yngc0der.phpcondition
```

В результате:
1. файлы модуля загружены в директорию ``bitrix/modules/yngc0der.phpcondition``
2. модуль зарегистрирован в системе
3. установлены необходимые обработчики событий

# Использование
При создании правила работы с корзиной в блоке "Дополнительные условия" 
необходимо добавить условие "PHP выражение", где в поле значения вписать PHP выражение,
которое возвращает логическое значение. Например, `isPromopage()`,
где isPromopage - определенная функция, которая возвращает тип `bool`
