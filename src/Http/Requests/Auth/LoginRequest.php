<?php

namespace RaDevs\JwtAuth\Http\Requests\Auth;


use RaDevs\JwtAuth\Http\Requests\BaseApiRequest;


class LoginRequest extends BaseApiRequest
{
    public function rules(): array
    {
        $configFields = config('ra-jwt-auth.login.fields', []);
        $searchFields = config('ra-jwt-auth.login.search_fields', ['email']);

        // Если в конфиге есть поля, используем их
        if (!empty($configFields)) {
            // Если есть несколько полей для поиска, нужно убедиться, что хотя бы одно заполнено
            if (count($searchFields) > 1) {
                $rules = $configFields;
                // Для каждого поля поиска (кроме первого) добавляем required_without_all
                foreach ($searchFields as $index => $field) {
                    if ($index === 0) {
                        // Первое поле - required_without_all остальных
                        $otherFields = array_slice($searchFields, 1);
                        if (isset($rules[$field])) {
                            $rules[$field] = $this->addRequiredWithoutAll($rules[$field], $otherFields);
                        }
                    } else {
                        // Остальные поля - required_without_all остальных
                        $otherFields = array_values(array_diff($searchFields, [$field]));
                        if (isset($rules[$field])) {
                            $rules[$field] = $this->addRequiredWithoutAll($rules[$field], $otherFields);
                        }
                    }
                }
                return $rules;
            }
            return $configFields;
        }

        // Fallback на дефолтные правила, если конфиг не настроен
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ];
    }

    /**
     * Добавить правило required_without_all к существующему правилу
     *
     * @param string|array $rule
     * @param array $otherFields
     * @return string|array
     */
    private function addRequiredWithoutAll($rule, array $otherFields): string|array
    {
        $requiredWithoutAll = 'required_without_all:' . implode(',', $otherFields);
        
        if (is_string($rule)) {
            // Если правило уже содержит required, заменяем его
            if (str_contains($rule, 'required')) {
                $rule = preg_replace('/required[^|]*/', $requiredWithoutAll, $rule);
            } else {
                $rule = $requiredWithoutAll . '|' . $rule;
            }
        } elseif (is_array($rule)) {
            // Для массива правил
            $hasRequired = false;
            foreach ($rule as $key => $value) {
                if (is_string($value) && str_contains($value, 'required')) {
                    $rule[$key] = preg_replace('/required[^|]*/', $requiredWithoutAll, $value);
                    $hasRequired = true;
                    break;
                }
            }
            if (!$hasRequired) {
                array_unshift($rule, $requiredWithoutAll);
            }
        }
        
        return $rule;
    }

    /**
     * Получить массив полей для поиска пользователя
     *
     * @return array
     */
    public function getSearchFields(): array
    {
        return config('ra-jwt-auth.login.search_fields', ['email']);
    }

    /**
     * Попытаться найти пользователя по полям поиска
     * Возвращает массив с найденным полем и значением, или null если ничего не найдено
     *
     * @param \RaDevs\JwtAuth\Repositories\Contracts\IUserRepository $userRepository
     * @return array|null ['field' => string, 'value' => string] или null
     */
    public function findUserBySearchFields($userRepository): ?array
    {
        $searchFields = $this->getSearchFields();
        $validated = $this->validated();

        foreach ($searchFields as $field) {
            if (isset($validated[$field]) && !empty($validated[$field])) {
                $value = $validated[$field];
                $user = $userRepository->getActivatedUserByField($field, $value);
                if ($user) {
                    return ['field' => $field, 'value' => $value, 'user' => $user];
                }
            }
        }

        return null;
    }
}