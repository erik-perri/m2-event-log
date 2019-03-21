<?php

namespace Ryvon\EventLog\Helper\Group\Template;

interface TemplateInterface
{
    public function getGroupTemplateFile(): string;

    public function getGroupBlockClass(): string;

    public function getHeaderTemplateFile(): string;

    public function getHeaderBlockClass(): string;

    public function getEntryTemplateFile(): string;

    public function getEntryBlockClass(): string;
}
