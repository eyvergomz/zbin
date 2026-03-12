<?php declare(strict_types=1);
/**
 * Filtros de Datos de Zbin
 *
 * Proporciona funciones para formatear datos de manera legible para el usuario.
 * Incluye conversion de tiempos y tamanos de archivos a formatos comprensibles.
 *
 * Ejemplo de uso:
 *   Filter::formatHumanReadableTime('5min')  => "5 minutos" (localizado)
 *   Filter::formatHumanReadableSize(1048576) => "1.05 MB"
 *
 * 
 */

namespace Zbin;

use Exception;

/**
 * Filter (Filtro)
 *
 * Funciones estaticas para formatear y filtrar datos.
 */
class Filter
{
    /**
     * Convierte una cadena de tiempo a una etiqueta legible y localizada
     *
     * Acepta formatos como "[numero][unidad]" donde la unidad puede ser:
     * sec (segundos), min (minutos), hour (horas), day (dias),
     * week (semanas), month (meses), year (anos)
     *
     * El resultado se traduce automaticamente al idioma activo del usuario
     * y se aplican las formas plurales correctas segun el idioma.
     *
     * @param  string $time Cadena de tiempo (ej: "5min", "1hour", "1week")
     * @throws Exception Si el formato de tiempo no es valido
     * @return string Tiempo formateado y traducido (ej: "5 minutes")
     */
    public static function formatHumanReadableTime($time)
    {
        if (preg_match('/^(\d+) *(\w+)$/', $time, $matches) !== 1) {
            throw new Exception("Error parsing time format '$time'", 30);
        }
        // Determinar la unidad de tiempo en singular
        switch ($matches[2]) {
            case 'sec':
                $unit = 'second';
                break;
            case 'min':
                $unit = 'minute';
                break;
            default:
                // Para otras unidades, quitar la 's' final si existe (plurals en ingles)
                $unit = rtrim($matches[2], 's');
        }
        // Usar I18n para traducir con soporte de plurales: [singular, plural]
        return I18n::_(array('%d ' . $unit, '%d ' . $unit . 's'), (int) $matches[1]);
    }

    /**
     * Convierte bytes a formato legible usando notacion IEC 80000-13:2008
     *
     * Divide sucesivamente por 1000 hasta encontrar la unidad apropiada.
     * Las unidades son: B, kB, MB, GB, TB, PB, EB, ZB, YB
     *
     * Ejemplo: 10485760 => "10.49 MB"
     *
     * @param  int $size Tamano en bytes
     * @return string Tamano formateado con unidad (ej: "10.49 MB")
     */
    public static function formatHumanReadableSize($size)
    {
        $iec = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $i   = 0;
        // Dividir por 1000 hasta encontrar la unidad adecuada
        while (($size / 1000) >= 1) {
            $size = $size / 1000;
            ++$i;
        }
        // Formatear con decimales solo si no son bytes puros
        return number_format($size, $i ? 2 : 0, '.', ' ') . ' ' . I18n::_($iec[$i]);
    }
}
