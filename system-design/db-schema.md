```mermaid
erDiagram
    users {
        int id PK
        string username
        string email
        datetime created_at
        datetime updated_at
    }

    anonymous_users {
        int id PK
        string email
        string name
        datetime created_at
        datetime updated_at
    }

    contacts {
        int id PK
        int own_user_id FK
        int knows_user_id FK
        int knows_anonymous_users_id FK
        string email
        string name
        datetime created_at
        datetime updated_at
    }

    signs {
        int id PK
        int user_id FK
        int anonymous_user_id FK
        datetime created_at
        datetime updated_at
    }

    documents {
        int id PK
        string title
        int owner_user_id FK
        string description
        DocumentStatus status
        datetime completed_at
        datetime created_at
        datetime updated_at
    }

    document_signers {
        int id PK
        int document_id FK
        int contact_id FK
        datetime created_at
        datetime updated_at
    }

    signer_document_fields {
        int id PK
        int document_signer_id FK
        int page
        float x
        float y
        float width
        float height
        DocumentFieldType type
        string label
        string description
        boolean required
        string value_signature_sign_id FK
        string value_initials
        string value_text
        boolean value_checkbox
        date value_date
        datetime created_at
        datetime updated_at
    }

    document_logs {
        int id PK
        int contact_id FK
        int document_id FK
        string ip
        datetime date
        Icon icon
        string text
        datetime created_at
        datetime updated_at
    }

    %% Beziehungen
    users ||--o{ documents                   : "owns"
    users ||--o{ contacts                    : "from_user"
    users ||--o{ contacts                    : "knows_user"
    anonymous_users ||--o{ contacts          : "knows_anonymous"
    users ||--o{ signs                       : "as_user"
    anonymous_users ||--o{ signs             : "as_anonymous"
    documents ||--o{ document_signers        : "has_signers"
    contacts ||--o{ document_signers         : "is_signer"
    document_signers ||--o{ signer_document_fields : "has_fields"
    signs ||--o{ signer_document_fields : "used_for"
    documents ||--o{ document_logs           : "has_logs"
    contacts ||--o{ document_logs            : "logged_by"
```

````mermaid
classDiagram
    class Icon {
        <<enum>>
        +create
        +send
        +watch
        +checkmark
    }

    class DocumentStatus {
        <<enum>>
        +draft
        +open
        +completed
        +template
    }

    class DocumentFieldType {
        <<enum>>
        +signature
        +initials
        +text
        +checkbox
        +date
    }
	```
````
