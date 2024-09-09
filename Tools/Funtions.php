<?php

function ValidarCadenas(...$strings) 
  {
      $pattern = '/(;\s*|\'.*?\'|--.*?$|\/\*.*?\*\/|\bxp_\w+\b|[\%<>~=])/m';
  
      foreach ($strings as $value) 
      {      
          if (is_array($value)) 
          {
              foreach ($value as $Clave => $Valor) 
              {
                  if (preg_match($pattern, $Clave)) { return false; }
                  
                  if (!ValidarCadenas($Valor)) { return false; }
              }
          }
  
          else 
          {
              if (preg_match($pattern, $value)) { return false; }
          }
      }
  
      return true;
  }