<?php

namespace abp\component;

interface FormInterface
{
    public function validate(array $data): bool;

    public function execute(): bool;
}
