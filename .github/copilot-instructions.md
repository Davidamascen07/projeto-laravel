# Copilot Instructions

<!-- Use this file to provide workspace-specific custom instructions to Copilot. For more details, visit https://code.visualstudio.com/docs/copilot/copilot-customization#_use-a-githubcopilotinstructionsmd-file -->

## Projeto E-commerce Laravel + WooCommerce Integration

Este é um projeto Laravel Full Stack que demonstra as competências necessárias para desenvolvimento de soluções de e-commerce com integrações avançadas.

### Contexto do Projeto
- **Backend**: Laravel com APIs RESTful
- **Integrações**: WordPress/WooCommerce API
- **Pagamentos**: Gateways de pagamento (Stripe, PagSeguro)
- **Arquitetura**: Clean Architecture, SOLID principles
- **Testes**: PHPUnit com Feature e Unit tests
- **Queue**: Jobs assíncronos e eventos
- **Frontend**: Admin dashboard com Vue.js/Blade

### Diretrizes de Código
1. **Seguir princípios SOLID** em todas as implementações
2. **Clean Code** com nomenclatura clara e funções pequenas
3. **Repository Pattern** para acesso a dados
4. **Service Layer** para lógica de negócio
5. **Events e Listeners** para desacoplamento
6. **Form Requests** para validação
7. **Resources** para transformação de dados da API
8. **Migrations** versionadas para banco de dados
9. **Seeders** para dados de desenvolvimento
10. **Testes automatizados** para todas as funcionalidades

### Estrutura Sugerida
- `app/Models/` - Eloquent models
- `app/Http/Controllers/Api/` - API controllers
- `app/Http/Controllers/Admin/` - Admin controllers
- `app/Services/` - Business logic services
- `app/Repositories/` - Data access layer
- `app/Events/` - Domain events
- `app/Listeners/` - Event handlers
- `app/Jobs/` - Queue jobs
- `app/Http/Requests/` - Form validation
- `app/Http/Resources/` - API resources
- `app/Integrations/` - External API integrations

### Padrões de Nomenclatura
- Controllers: `ProductController`, `OrderController`
- Models: `Product`, `Order`, `Customer`
- Services: `ProductService`, `PaymentService`
- Repositories: `ProductRepository`, `OrderRepository`
- Jobs: `SyncProductJob`, `ProcessPaymentJob`
- Events: `OrderCreated`, `ProductUpdated`
- Requests: `CreateProductRequest`, `UpdateOrderRequest`
- Resources: `ProductResource`, `OrderResource`

### Integração WooCommerce
- Usar client HTTP para APIs REST do WooCommerce
- Implementar sincronização bidirecional de produtos
- Gerenciar webhooks para atualizações em tempo real
- Mapear entidades entre Laravel e WooCommerce

### Sistema de Pagamentos
- Abstrair gateways de pagamento via interfaces
- Implementar padrão Strategy para diferentes provedores
- Gerenciar estados de pagamento via máquina de estados
- Logs detalhados para auditoria

Sempre priorize código testável, manutenível e seguindo as melhores práticas do Laravel.
