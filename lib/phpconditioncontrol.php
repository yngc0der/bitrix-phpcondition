<?php
/**
 * @author RG. <rg.archuser@gmail.com>
 */


namespace Yngc0der\PhpCondition;


use Bitrix\Main\Localization\Loc;


Loc::loadMessages(__FILE__);

class PhpConditionControl extends \CGlobalCondCtrlComplex
{
    const MODULE_ID = 'yngc0der.phpcondition';
    const CONTROL_ID = 'YC:PhpCond';

    public static function onBuildDiscountConditionInterfaceControls()
    {
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            static::GetControlDescr(),
            'main'
        );
    }

    public static function GetControlDescr()
    {
        $description = parent::GetControlDescr();
        $description['SORT'] = 100;

        return $description;
    }

    public static function GetClassName()
    {
        return get_called_class();
    }

    public static function GetControlShow($params)
    {
        $result = [];
        $controls = static::GetControls();

        if (empty($controls) || !is_array($controls)) {
            return $result;
        }

        foreach ($controls as $control_id => $data) {
            $row = [
                'controlId' => $data['ID'],
                'group' => false,
                'label' => $data['LABEL'],
                'showIn' => static::GetShowIn($params['SHOW_IN_GROUPS']),
                'control' => [],
            ];

            if (isset($data['PREFIX'])) {
                $row['control'][] = $data['PREFIX'];
            }

            if (empty($row['control'])) {
                $row['control'] = array_values($data['ATOMS']);
            } else {
                foreach ($data['ATOMS'] as $atom) {
                    $row['control'][] = $atom;
                }
                unset($atom);
            }

            $result[] = $row;
        }
        unset($control_id, $data, $controls);

        return $result;
    }

    public static function CheckCondition($params)
    {
        $php_code = "return {$params['VALUE']};";
        $check_condition = eval($php_code) ?? false;

        return boolval($check_condition);
    }

    public static function GetAtomsEx($control_id = false, $extended_mode = false)
    {
        $logic = static::GetLogic([
            BT_COND_LOGIC_EQ,
        ]);

        $atom_list = [
            static::CONTROL_ID => [
                'Logic' => [
                    'JS' => static::GetLogicAtom($logic),
                    'ATOM' => [
                        'ID' => 'logic',
                        'FIELD_TYPE' => 'string',
                        'FIELD_LENGTH' => 255,
                        'MULTIPLE' => 'N',
                        'VALIDATE' => 'list',
                    ],
                ],
                'Value' => [
                    'JS' => static::GetValueAtom([]),
                    'ATOM' => [
                        'ID' => 'value',
                        'FIELD_TYPE' => 'string',
                        'FIELD_LENGTH' => 255,
                        'MULTIPLE' => 'N',
                        'VALIDATE' => '',
                    ],
                ],
            ],
        ];

        return static::SearchControlAtoms($atom_list, $control_id, $extended_mode);
    }

    public static function Generate($condition, $params, $control, $childrens = false)
    {
        $control = static::GetControls($control);
        $control['ATOMS'] = static::GetAtomsEx($control['ID'], true);

        if (!is_array($control)) {
            return false;
        }

        $values = static::CheckAtoms($condition, $params, $control, false);

        if (!$values || empty($values['value'])) {
            return false;
        }

        $data = "[" .
            "'LOGIC' => '{$values['logic']}'," .
            "'VALUE' => '{$values['value']}'," .
            "]";
        $class_name = static::GetClassName();

        return "{$class_name}::CheckCondition({$data})";
    }

    public static function Parse($condition)
    {
        if (!isset($condition['controlId'])) {
            return false;
        }

        $atoms = static::GetAtomsEx($condition['controlId'], true);

        if (empty($atoms)) {
            return false;
        }

        $control = [
            'ID' => $condition['controlId'],
            'ATOMS' => $atoms,
        ];

        unset($atoms);

        return static::CheckAtoms($condition, $condition, $control, false);
    }

    public static function GetControls($control_id = false)
    {
        $atoms = static::GetAtomsEx();

        $control_list = [
            static::CONTROL_ID => [
                'ID' => static::CONTROL_ID,
                'LABEL' => Loc::getMessage('YC_PHP_COND_COND_LABEL'),
                'PREFIX' => Loc::getMessage('YC_PHP_COND_COND_PREFIX'),
                'EXECUTE_MODULE' => 'all',
                'MODULE_ID' => static::MODULE_ID,
                'MULTIPLE' => 'N',
                'GROUP' => 'N',
                'ATOMS' => $atoms[static::CONTROL_ID],
            ],
        ];

        return static::SearchControl($control_list, $control_id);
    }

    public static function GetConditionShow($params)
    {
        if (!isset($params['ID'])) {
            return false;
        }

        $atoms = static::GetAtomsEx($params['ID'], true);

        if (empty($atoms)) {
            return false;
        }

        $control = [
            'ID' => $params['ID'],
            'ATOMS' => $atoms,
        ];

        unset($atoms);

        return static::CheckAtoms($params['DATA'], $params, $control, true);
    }
}
