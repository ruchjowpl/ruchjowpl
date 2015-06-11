<?php

namespace RuchJow\JsonValidatorBundle\Services;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
//use Symfony\Component\DependencyInjection\Container;

/**
 * JSON validator class.
 *
 * @package RuchJow\JsonValidatorBundle\Services
 */
class JsonValidator
{

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine /*, Container $container*/)
    {
        $this->entityManager = $doctrine->getManager();
//        $this->container = $container;
    }


    /**
     * @param mixed  $data
     * @param array  $dataDef
     * @param array  &$error
     * @param string $name
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function validate($data, $dataDef, &$error = array(), $name = 'data')
    {
        switch ($dataDef['type']) {
            case 'array':
                if (!is_array($data)) {
                    $error = $this->prepareError('type', $dataDef,
                        'Invalid ' . $name . ' - not an array.');

                    return false;
                }
                if (isset($dataDef['minCount']) && count($data) < $dataDef['minCount']) {
                    $error = $this->prepareError('minCount', $dataDef,
                        'Invalid ' . $name . ' - array too short.');

                    return false;
                }
                if (isset($dataDef['maxCount']) && count($data) > $dataDef['maxCount']) {
                    $error = $this->prepareError('maxCount', $dataDef,
                        'Invalid ' . $name . ' - array too long.');

                    return false;
                }

                foreach ($dataDef['children'] as $key => $subDefinition) {
                    if ($key !== '#default') {
                        if (isset($data[$key])) {

                            if (!$this->validate($data[$key], $subDefinition, $error, $name . ' > ' . $key)) {
                                return false;
                            }

                        } elseif (!isset($subDefinition['optional']) || !$subDefinition['optional']) {
                            // By default all params are mandatory.
                            $error = $this->prepareError('not_optional', $subDefinition,
                                'Invalid ' . $name . ' - do not contain not optional ' . $key . '.');

                            return false;
                        }

                        unset($data[$key]);
                    }
                }

                if (isset($dataDef['children']['#default'])) {
                    foreach ($data as $key => $element) {
                        if (!$this->validate($element, $dataDef['children']['#default'], $error, $name . ' > #default(' . $key . ')')) {
                            return false;
                        }
                    }
                }

                break;

            case 'entityId':
                if (!is_int($data) || $data < 0) {
                    $error = array(
                        'message' => 'Invalid ' . $name . ' - invalid entity id.',
                    );

                    return false;
                }

                $entity = $this->entityManager->getRepository($dataDef['entity'])->find($data);
                if (!$entity) {
                    $error = array(
                        'message' => 'Invalid ' . $name . ' - entity not found.',
                    );

                    return false;
                }

                break;

            case 'int':
            case 'integer':
                if (!is_int($data)) {
                    $error = $this->prepareError('type', $dataDef,
                        'Invalid ' . $name . ' - expected integer.');

                    return false;
                }
                break;

            case 'decimal':
                if (!is_numeric($data)) {
                    $error = $this->prepareError('type', $dataDef,
                        'Invalid ' . $name . ' - expected decimal.');

                    return false;
                }
                break;

            case 'date':
                try {
                    $data = new \DateTime($data);
                } catch (\Exception $e) {
                    $error = $this->prepareError('type', $dataDef,
                        'Invalid ' . $name . ' - expected string representing a date.');

                    return false;
                }
                break;

            case 'string':
                if (!is_string($data)) {
                    $error = $this->prepareError('type', $dataDef,
                        'Invalid ' . $name . ' - expected string.');

                    return false;
                }
                break;


            case 'bool':
            case 'boolean':
                if (!is_bool($data)) {
                    $error = $this->prepareError('type', $dataDef,
                        'Invalid ' . $name . ' - expected boolean.');

                    return false;
                }
                break;

            default:
                throw new \InvalidArgumentException('Invalid data definition.');
        }


        // regexp pattern
        if (
            $dataDef['type'] === 'string'
            && isset($dataDef['pattern'])
            && preg_match($dataDef['pattern'], $data) !== 1
        ) {
            $error = $this->prepareError('pattern', $dataDef,
                'Invalid ' . $name . ' - string do not match pattern \'' . $dataDef['pattern'] . '\'.');

            return false;
        }


        // >, >=, <, <=, ==
        if (in_array($dataDef['type'], array('int', 'integer', 'decimal'))) {
            if (isset($dataDef['>']) && $data <= $dataDef['>']) {
                $error = $this->prepareError('>', $dataDef,
                    'Invalid ' . $name . ' - value must be greater than ' . $dataDef['>'] . '.');

                return false;
            }
            if (isset($dataDef['>=']) && $data < $dataDef['>=']) {
                $error = $this->prepareError('>=', $dataDef,
                    'Invalid ' . $name . ' - value must be greater than or equal to ' . $dataDef['>='] . '.');

                return false;
            }
            if (isset($dataDef['<']) && $data >= $dataDef['<']) {
                $error = $this->prepareError('<', $dataDef,
                    'Invalid ' . $name . ' - value must be lower than ' . $dataDef['<'] . '.');

                return false;
            }
            if (isset($dataDef['<=']) && $data > $dataDef['<=']) {
                $error = $this->prepareError('<=', $dataDef,
                    'Invalid ' . $name . ' - value must be lower than or equal to ' . $dataDef['<='] . '.');

                return false;
            }
            if (isset($dataDef['==']) && $data != $dataDef['==']) {
                $error = $this->prepareError('==', $dataDef,
                    'Invalid ' . $name . ' - value must be equal to' . $dataDef['=='] . '.');

                return false;
            }
        }

        // IN
        if (
            in_array($dataDef['type'], array('int', 'integer', 'decimal', 'string', 'bool', 'boolean'))
            && isset($dataDef['in'])
            && !in_array($data, $dataDef['in'], true)
        ) {
            $error = $this->prepareError('in', $dataDef,
                'Invalid ' . $name . ' - it\'s not one of defined allowed values.');

            return false;
        }

        return true;
    }

    /**
     * @param string      $type
     * @param mixed       $dataDef
     * @param string|null $defaultMessage
     * @param mixed|null  $defaultValue
     *
     * @return array
     */
    protected function prepareError($type, $dataDef, $defaultMessage = null, $defaultValue = null)
    {
        $defaultMsgKey   = 'error_msg';
        $defaultValueKey = 'error_value';

        $msgKey   = ($type ? $type . '_' : '') . 'error_msg';
        $valueKey = ($type ? $type . '_' : '') . 'error_value';


        return array(
            'message' => isset($dataDef[$msgKey]) ? $dataDef[$msgKey] :
                (isset($dataDef[$defaultMsgKey]) ? $dataDef[$defaultMsgKey] : $defaultMessage),
            'value'   => isset($dataDef[$valueKey]) ? $dataDef[$valueKey] :
                (isset($dataDef[$defaultValueKey]) ? $dataDef[$defaultValueKey] : $defaultValue),
        );
    }
}