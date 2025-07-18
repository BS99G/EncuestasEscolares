<?php
require_once __DIR__ . '/../core/Model.php';

class Survey extends Model
{
    /**
     * Obtiene todas las preguntas activas de la encuesta activa
     */
    public function obtenerPreguntasActivas(): array
    {
        $stmt = $this->db->query("
            SELECT p.id, p.texto, p.tipo 
            FROM preguntas p 
            INNER JOIN encuestas e ON e.id = p.encuesta_id 
            WHERE e.activa = 1
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene las opciones asociadas a una pregunta
     */
    public function obtenerOpciones(int $preguntaId): array
    {
        $stmt = $this->db->prepare("SELECT texto FROM opciones WHERE pregunta_id = ?");
        $stmt->execute([$preguntaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Guarda una respuesta individual
     */
    public function guardarRespuesta(string $matricula, int $preguntaId, string $respuesta): bool
    {
        // Validar longitud por si hay límites en DB
        if (strlen($respuesta) > 255) {
            $respuesta = substr($respuesta, 0, 255);
        }

        $stmt = $this->db->prepare("
            INSERT INTO respuestas (matricula, pregunta_id, respuesta)
            VALUES (:matricula, :pregunta_id, :respuesta)
        ");

        return $stmt->execute([
            ':matricula' => $matricula,
            ':pregunta_id' => $preguntaId,
            ':respuesta' => $respuesta
        ]);
    }

    /**
     * Verifica si un alumno ya respondió la encuesta activa
     */
    public function yaRespondio(string $matricula): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM respuestas r
            INNER JOIN preguntas p ON r.pregunta_id = p.id
            INNER JOIN encuestas e ON p.encuesta_id = e.id
            WHERE r.matricula = ? AND e.activa = 1
        ");
        $stmt->execute([$matricula]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Verifica si la matrícula existe en la base de datos
     */
    public function validarMatricula(string $matricula): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE matricula = ?");
        $stmt->execute([$matricula]);
        return $stmt->fetchColumn() > 0;
    }
}
