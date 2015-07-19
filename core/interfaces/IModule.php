<?php
interface IModule {
    public function __construct(Request $request);
    public function getResponse();
}