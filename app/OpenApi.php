<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.1.0',
    description: 'API REST del Sistema de Gestión Académica para centros infantiles. Los endpoints protegidos usan tokens Bearer de Laravel Sanctum.',
    title: 'SGA API',
)]
#[OA\Server(url: '/', description: 'Servidor de la aplicación')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    description: 'Token personal obtenido en POST /api/login.',
    bearerFormat: 'Bearer',
    scheme: 'bearer',
)]
#[OA\Tag(name: 'Autenticación', description: 'Inicio, cierre de sesión y establecimiento de contraseña')]
#[OA\Tag(name: 'Público', description: 'Catálogo público y solicitudes de admisión')]
#[OA\Tag(name: 'Representante', description: 'Portal de representantes')]
#[OA\Tag(name: 'Docente', description: 'Portal de docentes')]
#[OA\Tag(name: 'Administración', description: 'Panel y catálogos administrativos')]
#[OA\Tag(name: 'Notificaciones', description: 'Suscripciones Web Push')]
#[OA\Schema(
    schema: 'Message',
    required: ['message'],
    properties: [new OA\Property(property: 'message', type: 'string')],
    type: 'object',
)]
#[OA\Schema(
    schema: 'ValidationError',
    required: ['message', 'errors'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Los datos proporcionados no son válidos.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(type: 'array', items: new OA\Items(type: 'string')),
        ),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'PaginatedCollection',
    required: ['current_page', 'data', 'last_page', 'per_page', 'total'],
    properties: [
        new OA\Property(property: 'current_page', type: 'integer', example: 1),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
        new OA\Property(property: 'last_page', type: 'integer', example: 1),
        new OA\Property(property: 'per_page', type: 'integer', example: 15),
        new OA\Property(property: 'total', type: 'integer'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'User',
    required: ['id', 'name', 'email', 'identification', 'role', 'is_active'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', readOnly: true, example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'María Pérez'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'maria@example.com'),
        new OA\Property(property: 'identification', type: 'string', example: '1751234567'),
        new OA\Property(property: 'phone', type: 'string', example: '0991234567', nullable: true),
        new OA\Property(property: 'address', type: 'string', nullable: true),
        new OA\Property(property: 'role', type: 'string', enum: ['admin', 'representative', 'teacher']),
        new OA\Property(property: 'is_active', type: 'boolean'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', readOnly: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', readOnly: true),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'Level',
    required: ['name', 'max_capacity', 'student_aux_ratio', 'enrollment_fee', 'monthly_fee'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', readOnly: true),
        new OA\Property(property: 'name', type: 'string', example: 'Inicial 2', maxLength: 100),
        new OA\Property(property: 'sequence_order', type: 'integer', nullable: true, example: 4),
        new OA\Property(property: 'next_level_id', type: 'integer', nullable: true),
        new OA\Property(property: 'max_capacity', type: 'integer', minimum: 1, example: 25),
        new OA\Property(property: 'student_aux_ratio', type: 'integer', minimum: 0, example: 10),
        new OA\Property(property: 'enrollment_fee', type: 'number', format: 'float', minimum: 0, example: 80),
        new OA\Property(property: 'monthly_fee', type: 'number', format: 'float', example: 150, minimum: 0),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'Representative',
    required: ['id_card', 'first_name', 'last_name', 'email'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', readOnly: true),
        new OA\Property(property: 'user_id', type: 'integer', readOnly: true, nullable: true),
        new OA\Property(property: 'id_card', type: 'string', maxLength: 50),
        new OA\Property(property: 'first_name', type: 'string', maxLength: 100),
        new OA\Property(property: 'last_name', type: 'string', maxLength: 100),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'phone', type: 'string', nullable: true),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'Teacher',
    required: ['id_card', 'first_name', 'last_name', 'email', 'teacher_type'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', readOnly: true),
        new OA\Property(property: 'user_id', type: 'integer', nullable: true, readOnly: true),
        new OA\Property(property: 'id_card', type: 'string', maxLength: 50),
        new OA\Property(property: 'first_name', type: 'string', maxLength: 100),
        new OA\Property(property: 'last_name', type: 'string', maxLength: 100),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'teacher_type', type: 'string', enum: ['principal', 'auxiliary']),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'Course',
    required: ['level_id', 'parallel'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', readOnly: true),
        new OA\Property(property: 'level_id', type: 'integer'),
        new OA\Property(property: 'parallel', type: 'string', maxLength: 10, example: 'A'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'CourseTeacher',
    required: ['course_id', 'teacher_id', 'assigned_role'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', readOnly: true),
        new OA\Property(property: 'course_id', type: 'integer'),
        new OA\Property(property: 'teacher_id', type: 'integer'),
        new OA\Property(property: 'assigned_role', type: 'string', enum: ['principal', 'auxiliary']),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'Enrollment',
    required: ['student_id', 'level_id', 'enrollment_date', 'status'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', readOnly: true),
        new OA\Property(property: 'student_id', type: 'integer'),
        new OA\Property(property: 'academic_period_id', type: 'integer', nullable: true),
        new OA\Property(property: 'level_id', type: 'integer'),
        new OA\Property(property: 'course_id', type: 'integer', nullable: true, description: 'Se asigna automáticamente al cierre; el representante no lo elige.'),
        new OA\Property(property: 'enrollment_date', type: 'string', format: 'date'),
        new OA\Property(property: 'status', type: 'string', enum: ['draft', 'pending_payment', 'payment_in_progress', 'paid_pending_assignment', 'active', 'finalized', 'withdrawn', 'graduated']),
        new OA\Property(property: 'level_outcome', type: 'string', enum: ['completed', 'not_completed'], nullable: true),
        new OA\Property(property: 'assignment_issue', type: 'string', nullable: true),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'AcademicPeriod',
    required: ['name', 'starts_on', 'ends_on', 'enrollment_opens_at', 'enrollment_closes_at', 'status'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', readOnly: true),
        new OA\Property(property: 'name', type: 'string', example: '2026-2027'),
        new OA\Property(property: 'starts_on', type: 'string', format: 'date'),
        new OA\Property(property: 'ends_on', type: 'string', format: 'date'),
        new OA\Property(property: 'enrollment_opens_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'enrollment_closes_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'status', type: 'string', enum: ['draft', 'open', 'closed', 'allocating', 'allocated']),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'EnrollmentSection',
    description: 'Datos validados de una sección de la ficha integral. Las secciones permitidas son student, health, conditions, allergies, medications, address, legal_representative, billing, emergency_contacts e insurance.',
    type: 'object',
    additionalProperties: new OA\AdditionalProperties,
)]
#[OA\Schema(
    schema: 'Tuition',
    required: ['student_id', 'amount', 'generation_date', 'due_date'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', readOnly: true),
        new OA\Property(property: 'student_id', type: 'integer'),
        new OA\Property(property: 'enrollment_id', type: 'integer', nullable: true),
        new OA\Property(property: 'concept', type: 'string', enum: ['enrollment', 'monthly']),
        new OA\Property(property: 'amount', type: 'number', format: 'float', minimum: 0),
        new OA\Property(property: 'generation_date', type: 'string', format: 'date'),
        new OA\Property(property: 'due_date', type: 'string', format: 'date'),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'partial', 'paid'], default: 'pending'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'Payment',
    required: ['tuition_id', 'payment_method', 'amount_paid', 'payment_date'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', readOnly: true),
        new OA\Property(property: 'tuition_id', type: 'integer'),
        new OA\Property(property: 'payment_method', type: 'string', enum: ['cash', 'transfer', 'payphone']),
        new OA\Property(property: 'amount_paid', type: 'number', format: 'float', minimum: 0),
        new OA\Property(property: 'payment_date', type: 'string', format: 'date'),
        new OA\Property(property: 'reference_number', type: 'string', nullable: true, maxLength: 100),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'AcademicReport',
    required: ['student_id', 'teacher_id', 'development_area', 'evaluated_skill', 'achievement_level'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', readOnly: true),
        new OA\Property(property: 'student_id', type: 'integer'),
        new OA\Property(property: 'teacher_id', type: 'integer'),
        new OA\Property(property: 'development_area', type: 'string', maxLength: 100),
        new OA\Property(property: 'evaluated_skill', type: 'string', maxLength: 100),
        new OA\Property(property: 'achievement_level', type: 'string', maxLength: 100),
        new OA\Property(property: 'observations', type: 'string', nullable: true),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'LoginRequest',
    required: ['email', 'password', 'device_name'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'password', type: 'string', format: 'password'),
        new OA\Property(property: 'device_name', type: 'string', example: 'PWA'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'ResetPasswordRequest',
    required: ['email', 'token', 'password', 'password_confirmation'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'token', type: 'string'),
        new OA\Property(property: 'password', type: 'string', format: 'password'),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'PushSubscriptionRequest',
    required: ['endpoint'],
    properties: [
        new OA\Property(property: 'endpoint', type: 'string', format: 'uri'),
        new OA\Property(
            property: 'keys',
            required: ['p256dh', 'auth'],
            properties: [
                new OA\Property(property: 'p256dh', type: 'string'),
                new OA\Property(property: 'auth', type: 'string'),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
#[OA\Post(
    path: '/api/login',
    operationId: 'login',
    summary: 'Iniciar sesión',
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest')),
    tags: ['Autenticación'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Post(
    path: '/api/reset-password',
    operationId: 'resetPassword',
    summary: 'Establecer o restablecer contraseña',
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ResetPasswordRequest')),
    tags: ['Autenticación'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Message')), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Get(
    path: '/api/vapid-public-key',
    operationId: 'vapidPublicKey',
    summary: 'Obtener la clave pública VAPID',
    tags: ['Público'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa')],
)]
#[OA\Get(
    path: '/api/levels',
    operationId: 'publicLevels',
    summary: 'Listar niveles disponibles',
    tags: ['Público'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/PaginatedCollection'))],
)]
#[OA\Post(
    path: '/api/admissions',
    operationId: 'submitAdmission',
    summary: 'Enviar una solicitud de admisión',
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/Admission')),
    tags: ['Público'],
    responses: [new OA\Response(response: 201, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Admission')), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Post(
    path: '/api/logout',
    operationId: 'logout',
    summary: 'Cerrar sesión',
    security: [['sanctum' => []]],
    tags: ['Autenticación'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Message')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Get(
    path: '/api/user',
    operationId: 'currentUser',
    summary: 'Consultar el usuario autenticado',
    security: [['sanctum' => []]],
    tags: ['Autenticación'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/User')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Post(
    path: '/api/push/subscriptions',
    operationId: 'subscribePush',
    summary: 'Registrar una suscripción Web Push',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/PushSubscriptionRequest')),
    tags: ['Notificaciones'],
    responses: [new OA\Response(response: 201, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Message')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Delete(
    path: '/api/push/subscriptions',
    operationId: 'unsubscribePush',
    summary: 'Eliminar una suscripción Web Push',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/PushSubscriptionRequest')),
    tags: ['Notificaciones'],
    responses: [new OA\Response(response: 204, description: 'Operación exitosa'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Get(
    path: '/api/me/students',
    operationId: 'representativeStudents',
    summary: 'Listar estudiantes del representante',
    security: [['sanctum' => []]],
    tags: ['Representante'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Get(
    path: '/api/me/students/{student}/enrollment',
    operationId: 'representativeCurrentEnrollment',
    summary: 'Consultar el proceso de matrícula del estudiante sin exponer aforo ni selección de paralelo',
    security: [['sanctum' => []]],
    tags: ['Representante'],
    parameters: [new OA\Parameter(name: 'student', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Matrícula actual o null', content: new OA\JsonContent(ref: '#/components/schemas/Enrollment')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Post(
    path: '/api/me/students/{student}/enrollments',
    operationId: 'representativeStartEnrollment',
    summary: 'Iniciar o reanudar la ficha integral de matrícula para el nivel asignado',
    security: [['sanctum' => []]],
    tags: ['Representante'],
    parameters: [new OA\Parameter(name: 'student', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 201, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Enrollment')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Put(
    path: '/api/me/enrollments/{enrollment}/form/{section}',
    operationId: 'representativeSaveEnrollmentSection',
    summary: 'Guardar explícitamente una sección de la ficha de matrícula',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/EnrollmentSection')),
    tags: ['Representante'],
    parameters: [
        new OA\Parameter(name: 'enrollment', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        new OA\Parameter(name: 'section', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: ['student', 'health', 'conditions', 'allergies', 'medications', 'address', 'legal_representative', 'billing', 'emergency_contacts', 'insurance'])),
    ],
    responses: [new OA\Response(response: 200, description: 'Sección guardada', content: new OA\JsonContent(ref: '#/components/schemas/Enrollment')), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Post(
    path: '/api/me/enrollments/{enrollment}/complete',
    operationId: 'representativeCompleteEnrollmentForm',
    summary: 'Validar la ficha completa, registrar consentimientos y generar el rubro de matrícula',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['accept_privacy_policy', 'accept_medical_data_processing', 'accept_emergency_authorization'],
        properties: [
            new OA\Property(property: 'accept_privacy_policy', type: 'boolean', example: true),
            new OA\Property(property: 'accept_medical_data_processing', type: 'boolean', example: true),
            new OA\Property(property: 'accept_emergency_authorization', type: 'boolean', example: true),
        ],
    )),
    tags: ['Representante'],
    parameters: [new OA\Parameter(name: 'enrollment', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Ficha completada', content: new OA\JsonContent(ref: '#/components/schemas/Enrollment')), new OA\Response(response: 422, description: 'Ficha incompleta o pensiones vencidas', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Post(
    path: '/api/me/enrollments/{enrollment}/payphone',
    operationId: 'representativePayEnrollment',
    summary: 'Preparar en backend el pago PayPhone del rubro de matrícula',
    security: [['sanctum' => []]],
    tags: ['Representante'],
    parameters: [new OA\Parameter(name: 'enrollment', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'URL de redirección PayPhone'), new OA\Response(response: 422, description: 'Estado de matrícula inválido', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Get(
    path: '/api/me/tuitions',
    operationId: 'representativeTuitions',
    summary: 'Listar pensiones pendientes',
    security: [['sanctum' => []]],
    tags: ['Representante'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Get(
    path: '/api/me/students/{student}/reports',
    operationId: 'representativeReports',
    summary: 'Consultar reportes de un estudiante',
    security: [['sanctum' => []]],
    tags: ['Representante'],
    parameters: [new OA\Parameter(name: 'student', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Post(
    path: '/api/me/payments/payphone',
    operationId: 'payWithPayphone',
    summary: 'Pagar una o varias pensiones mensuales mediante PayPhone',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['tuition_ids'],
        properties: [new OA\Property(property: 'tuition_ids', type: 'array', items: new OA\Items(type: 'integer'))],
    )),
    tags: ['Representante'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Payment')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Get(
    path: '/api/teacher/courses',
    operationId: 'teacherCourses',
    summary: 'Listar cursos del docente',
    security: [['sanctum' => []]],
    tags: ['Docente'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Get(
    path: '/api/teacher/courses/{course}/students',
    operationId: 'teacherStudents',
    summary: 'Listar estudiantes de un curso',
    security: [['sanctum' => []]],
    tags: ['Docente'],
    parameters: [new OA\Parameter(name: 'course', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Get(
    path: '/api/teacher/reports',
    operationId: 'teacherReports',
    summary: 'Listar reportes emitidos por el docente',
    security: [['sanctum' => []]],
    tags: ['Docente'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/PaginatedCollection')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Get(
    path: '/api/teacher/students/{student}/care-profile',
    operationId: 'teacherStudentCareProfile',
    summary: 'Consultar únicamente la ficha de cuidado de un estudiante asignado al docente',
    security: [['sanctum' => []]],
    tags: ['Docente'],
    parameters: [new OA\Parameter(name: 'student', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Ficha médica y de cuidado; el acceso queda auditado'), new OA\Response(response: 403, description: 'El docente no está asignado al estudiante')],
)]
#[OA\Post(
    path: '/api/teacher/reports',
    operationId: 'teacherStoreReport',
    summary: 'Registrar un reporte académico',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/AcademicReport')),
    tags: ['Docente'],
    responses: [new OA\Response(response: 201, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/AcademicReport')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Get(
    path: '/api/admin/dashboard',
    operationId: 'adminDashboard',
    summary: 'Consultar indicadores administrativos',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Get(
    path: '/api/users',
    operationId: 'usersIndex',
    summary: 'Listar usuarios',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/PaginatedCollection')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Post(
    path: '/api/users',
    operationId: 'usersStore',
    summary: 'Crear usuarios',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/User')),
    tags: ['Administración'],
    responses: [new OA\Response(response: 201, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/User')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Get(
    path: '/api/users/{user}',
    operationId: 'usersShow',
    summary: 'Consultar usuarios',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/User')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Put(
    path: '/api/users/{user}',
    operationId: 'usersUpdate',
    summary: 'Actualizar usuarios',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/User')),
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/User')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Delete(
    path: '/api/users/{user}',
    operationId: 'usersDestroy',
    summary: 'Eliminar usuarios',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 204, description: 'Operación exitosa'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Get(
    path: '/api/representatives',
    operationId: 'representativesIndex',
    summary: 'Listar representantes',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/PaginatedCollection')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Post(
    path: '/api/representatives',
    operationId: 'representativesStore',
    summary: 'Crear representantes',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/Representative')),
    tags: ['Administración'],
    responses: [new OA\Response(response: 201, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Representative')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Get(
    path: '/api/representatives/{representative}',
    operationId: 'representativesShow',
    summary: 'Consultar representantes',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'representative', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Representative')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Put(
    path: '/api/representatives/{representative}',
    operationId: 'representativesUpdate',
    summary: 'Actualizar representantes',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/Representative')),
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'representative', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Representative')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Delete(
    path: '/api/representatives/{representative}',
    operationId: 'representativesDestroy',
    summary: 'Eliminar representantes',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'representative', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 204, description: 'Operación exitosa'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Get(
    path: '/api/teachers',
    operationId: 'teachersIndex',
    summary: 'Listar docentes',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/PaginatedCollection')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Post(
    path: '/api/teachers',
    operationId: 'teachersStore',
    summary: 'Crear docentes',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/Teacher')),
    tags: ['Administración'],
    responses: [new OA\Response(response: 201, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Teacher')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Get(
    path: '/api/teachers/{teacher}',
    operationId: 'teachersShow',
    summary: 'Consultar docentes',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'teacher', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Teacher')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Put(
    path: '/api/teachers/{teacher}',
    operationId: 'teachersUpdate',
    summary: 'Actualizar docentes',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/Teacher')),
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'teacher', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Teacher')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Delete(
    path: '/api/teachers/{teacher}',
    operationId: 'teachersDestroy',
    summary: 'Eliminar docentes',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'teacher', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 204, description: 'Operación exitosa'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Get(
    path: '/api/courses',
    operationId: 'coursesIndex',
    summary: 'Listar cursos',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/PaginatedCollection')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Post(
    path: '/api/courses',
    operationId: 'coursesStore',
    summary: 'Crear cursos',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/Course')),
    tags: ['Administración'],
    responses: [new OA\Response(response: 201, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Course')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Get(
    path: '/api/courses/{course}',
    operationId: 'coursesShow',
    summary: 'Consultar cursos',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'course', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Course')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Put(
    path: '/api/courses/{course}',
    operationId: 'coursesUpdate',
    summary: 'Actualizar cursos',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/Course')),
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'course', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Course')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Delete(
    path: '/api/courses/{course}',
    operationId: 'coursesDestroy',
    summary: 'Eliminar cursos',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'course', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 204, description: 'Operación exitosa'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Get(
    path: '/api/course-teachers',
    operationId: 'courseTeachersIndex',
    summary: 'Listar asignaciones docentes',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/PaginatedCollection')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Post(
    path: '/api/course-teachers',
    operationId: 'courseTeachersStore',
    summary: 'Crear asignaciones docentes',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CourseTeacher')),
    tags: ['Administración'],
    responses: [new OA\Response(response: 201, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/CourseTeacher')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Get(
    path: '/api/course-teachers/{course_teacher}',
    operationId: 'courseTeachersShow',
    summary: 'Consultar asignaciones docentes',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'course_teacher', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/CourseTeacher')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Put(
    path: '/api/course-teachers/{course_teacher}',
    operationId: 'courseTeachersUpdate',
    summary: 'Actualizar asignaciones docentes',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CourseTeacher')),
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'course_teacher', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/CourseTeacher')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Delete(
    path: '/api/course-teachers/{course_teacher}',
    operationId: 'courseTeachersDestroy',
    summary: 'Eliminar asignaciones docentes',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'course_teacher', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 204, description: 'Operación exitosa'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Get(
    path: '/api/students',
    operationId: 'studentsIndex',
    summary: 'Listar estudiantes',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/PaginatedCollection')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Post(
    path: '/api/students',
    operationId: 'studentsStore',
    summary: 'Crear estudiantes',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/Student')),
    tags: ['Administración'],
    responses: [new OA\Response(response: 201, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Student')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Get(
    path: '/api/students/{student}',
    operationId: 'studentsShow',
    summary: 'Consultar estudiantes',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'student', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Student')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Put(
    path: '/api/students/{student}',
    operationId: 'studentsUpdate',
    summary: 'Actualizar estudiantes',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/Student')),
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'student', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Student')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Delete(
    path: '/api/students/{student}',
    operationId: 'studentsDestroy',
    summary: 'Eliminar estudiantes',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'student', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 204, description: 'Operación exitosa'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Get(
    path: '/api/enrollments',
    operationId: 'enrollmentsIndex',
    summary: 'Listar matrículas',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/PaginatedCollection')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Post(
    path: '/api/enrollments',
    operationId: 'enrollmentsStore',
    summary: 'Crear matrículas',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/Enrollment')),
    tags: ['Administración'],
    responses: [new OA\Response(response: 201, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Enrollment')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Get(
    path: '/api/enrollments/{enrollment}',
    operationId: 'enrollmentsShow',
    summary: 'Consultar matrículas',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'enrollment', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Enrollment')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Put(
    path: '/api/enrollments/{enrollment}',
    operationId: 'enrollmentsUpdate',
    summary: 'Actualizar matrículas',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/Enrollment')),
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'enrollment', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Enrollment')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Delete(
    path: '/api/enrollments/{enrollment}',
    operationId: 'enrollmentsDestroy',
    summary: 'Eliminar matrículas',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'enrollment', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 204, description: 'Operación exitosa'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Get(
    path: '/api/tuitions',
    operationId: 'tuitionsIndex',
    summary: 'Listar pensiones',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/PaginatedCollection')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Post(
    path: '/api/tuitions',
    operationId: 'tuitionsStore',
    summary: 'Crear pensiones',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/Tuition')),
    tags: ['Administración'],
    responses: [new OA\Response(response: 201, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Tuition')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Get(
    path: '/api/tuitions/{tuition}',
    operationId: 'tuitionsShow',
    summary: 'Consultar pensiones',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'tuition', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Tuition')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Put(
    path: '/api/tuitions/{tuition}',
    operationId: 'tuitionsUpdate',
    summary: 'Actualizar pensiones',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/Tuition')),
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'tuition', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Tuition')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Delete(
    path: '/api/tuitions/{tuition}',
    operationId: 'tuitionsDestroy',
    summary: 'Eliminar pensiones',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'tuition', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 204, description: 'Operación exitosa'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Get(
    path: '/api/payments',
    operationId: 'paymentsIndex',
    summary: 'Listar pagos',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/PaginatedCollection')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Post(
    path: '/api/payments',
    operationId: 'paymentsStore',
    summary: 'Crear pagos',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/Payment')),
    tags: ['Administración'],
    responses: [new OA\Response(response: 201, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Payment')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Get(
    path: '/api/payments/{payment}',
    operationId: 'paymentsShow',
    summary: 'Consultar pagos',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'payment', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Payment')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Put(
    path: '/api/payments/{payment}',
    operationId: 'paymentsUpdate',
    summary: 'Actualizar pagos',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/Payment')),
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'payment', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Payment')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Delete(
    path: '/api/payments/{payment}',
    operationId: 'paymentsDestroy',
    summary: 'Eliminar pagos',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'payment', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 204, description: 'Operación exitosa'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Get(
    path: '/api/academic-reports',
    operationId: 'academicReportsIndex',
    summary: 'Listar reportes académicos',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/PaginatedCollection')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Post(
    path: '/api/academic-reports',
    operationId: 'academicReportsStore',
    summary: 'Crear reportes académicos',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/AcademicReport')),
    tags: ['Administración'],
    responses: [new OA\Response(response: 201, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/AcademicReport')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Get(
    path: '/api/academic-reports/{academic_report}',
    operationId: 'academicReportsShow',
    summary: 'Consultar reportes académicos',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'academic_report', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/AcademicReport')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Put(
    path: '/api/academic-reports/{academic_report}',
    operationId: 'academicReportsUpdate',
    summary: 'Actualizar reportes académicos',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/AcademicReport')),
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'academic_report', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/AcademicReport')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Delete(
    path: '/api/academic-reports/{academic_report}',
    operationId: 'academicReportsDestroy',
    summary: 'Eliminar reportes académicos',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'academic_report', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 204, description: 'Operación exitosa'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Post(
    path: '/api/levels',
    operationId: 'levelsStore',
    summary: 'Crear niveles',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/Level')),
    tags: ['Administración'],
    responses: [new OA\Response(response: 201, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Level')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Get(
    path: '/api/levels/{level}',
    operationId: 'levelsShow',
    summary: 'Consultar niveles',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'level', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Level')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Put(
    path: '/api/levels/{level}',
    operationId: 'levelsUpdate',
    summary: 'Actualizar niveles',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/Level')),
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'level', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Level')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Delete(
    path: '/api/levels/{level}',
    operationId: 'levelsDestroy',
    summary: 'Eliminar niveles',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'level', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 204, description: 'Operación exitosa'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Get(
    path: '/api/admissions',
    operationId: 'admissionsIndex',
    summary: 'Listar admisiones',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/PaginatedCollection')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Get(
    path: '/api/admissions/{admission}',
    operationId: 'admissionsShow',
    summary: 'Consultar admisiones',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'admission', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Admission')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Put(
    path: '/api/admissions/{admission}',
    operationId: 'admissionsUpdate',
    summary: 'Actualizar admisiones',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/Admission')),
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'admission', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Admission')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado'), new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Delete(
    path: '/api/admissions/{admission}',
    operationId: 'admissionsDestroy',
    summary: 'Eliminar admisiones',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'admission', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 204, description: 'Operación exitosa'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Post(
    path: '/api/admissions/{admission}/approve',
    operationId: 'approveAdmission',
    summary: 'Aprobar una admisión',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'admission', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Admission')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Post(
    path: '/api/admissions/{admission}/reject',
    operationId: 'rejectAdmission',
    summary: 'Rechazar una admisión',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'admission', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa', content: new OA\JsonContent(ref: '#/components/schemas/Admission')), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\Post(
    path: '/api/academic-periods',
    operationId: 'academicPeriodsStore',
    summary: 'Crear un periodo académico y su ventana de matrícula sin solapamientos',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/AcademicPeriod')),
    tags: ['Administración'],
    responses: [new OA\Response(response: 201, description: 'Periodo creado', content: new OA\JsonContent(ref: '#/components/schemas/AcademicPeriod')), new OA\Response(response: 422, description: 'Periodo solapado', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))],
)]
#[OA\Post(
    path: '/api/academic-periods/{academicPeriod}/assignments/run',
    operationId: 'academicPeriodRunAssignments',
    summary: 'Ejecutar la asignación automática por menor ocupación y luego menor ID',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'force', type: 'boolean', default: false),
            new OA\Property(property: 'reason', type: 'string', nullable: true),
        ],
    )),
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'academicPeriod', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Conteos de matrículas asignadas y pendientes'), new OA\Response(response: 422, description: 'El periodo aún no cerró')],
)]
#[OA\Put(
    path: '/api/enrollments/{enrollment}/assignment',
    operationId: 'enrollmentManualAssignment',
    summary: 'Asignar o reasignar manualmente un paralelo con motivo auditado',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['course_id', 'reason'],
        properties: [new OA\Property(property: 'course_id', type: 'integer'), new OA\Property(property: 'reason', type: 'string')],
    )),
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'enrollment', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Matrícula asignada', content: new OA\JsonContent(ref: '#/components/schemas/Enrollment')), new OA\Response(response: 422, description: 'Nivel o aforo inválido')],
)]
#[OA\Put(
    path: '/api/enrollments/{enrollment}/outcome',
    operationId: 'enrollmentOutcome',
    summary: 'Registrar resultado y promover automáticamente al siguiente nivel secuencial',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['outcome'],
        properties: [
            new OA\Property(property: 'outcome', type: 'string', enum: ['completed', 'not_completed']),
            new OA\Property(property: 'override_level_id', type: 'integer', nullable: true),
            new OA\Property(property: 'reason', type: 'string', nullable: true),
        ],
    )),
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'enrollment', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Resultado registrado', content: new OA\JsonContent(ref: '#/components/schemas/Enrollment')), new OA\Response(response: 422, description: 'Estado inválido')],
)]
#[OA\Put(
    path: '/api/enrollments/{enrollment}/exception',
    operationId: 'enrollmentDeadlineException',
    summary: 'Conceder una excepción temporal auditada a una ficha pendiente',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['exception_until', 'reason'],
        properties: [
            new OA\Property(property: 'exception_until', type: 'string', format: 'date-time'),
            new OA\Property(property: 'reason', type: 'string'),
        ],
    )),
    tags: ['Administración'],
    parameters: [new OA\Parameter(name: 'enrollment', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Excepción concedida'), new OA\Response(response: 422, description: 'Estado o fecha inválida')],
)]
#[OA\Post(
    path: '/api/tuitions/generate',
    operationId: 'generateTuitions',
    summary: 'Generar pensiones mensuales',
    security: [['sanctum' => []]],
    tags: ['Administración'],
    responses: [new OA\Response(response: 200, description: 'Operación exitosa'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'No autorizado')],
)]
#[OA\PathItem(path: '/')]
class OpenApi
{
    // OpenAPI metadata and reusable components.
}
