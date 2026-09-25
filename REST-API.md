# MageSuite File Attachments – REST API

REST API for managing file attachments and their assignments to products.

---

## Table of contents

- [Authentication](#authentication)
- [Permissions (ACL)](#permissions-acl)
- [Data models](#data-models)
- [Attachments](#attachments)
  - [GET /V1/file-attachments – list attachments](#get-v1file-attachments)
  - [POST /V1/file-attachments – create an attachment](#post-v1file-attachments)
  - [PUT /V1/file-attachments/{attachmentId} – update an attachment](#put-v1file-attachmentsattachmentid)
  - [DELETE /V1/file-attachments/{attachmentId} – delete an attachment](#delete-v1file-attachmentsattachmentid)
- [Product assignments](#product-assignments)
  - [GET /V1/products/{sku}/file-attachments – list a product's attachments](#get-v1productsskufile-attachments)
  - [POST /V1/products/{sku}/file-attachments – add assignments](#post-v1productsskufile-attachments)
  - [POST /V1/products/{sku}/file-attachments/replace – replace assignments](#post-v1productsskufile-attachmentsreplace)
  - [DELETE /V1/products/{sku}/file-attachments/{attachmentId} – remove an assignment](#delete-v1productsskufile-attachmentsattachmentid)

---

## Authentication

All endpoints need a bearer token: an admin token or an integration access token.
Send the token with every request:

```
Authorization: Bearer <token>
```

## Permissions (ACL)

| ACL resource | Endpoints |
|---|---|
| `MageSuite_FileAttachments::attachment_view` | `GET /V1/file-attachments`, `GET /V1/products/{sku}/file-attachments` |
| `MageSuite_FileAttachments::attachment_save` | `POST /V1/file-attachments`, `PUT /V1/file-attachments/{attachmentId}`, `POST /V1/products/{sku}/file-attachments`, `POST /V1/products/{sku}/file-attachments/replace` |
| `MageSuite_FileAttachments::attachment_delete` | `DELETE /V1/file-attachments/{attachmentId}`, `DELETE /V1/products/{sku}/file-attachments/{attachmentId}` |

---

## Data models

### Attachment

Used as the request body for creating and updating attachments, and returned by every endpoint that returns attachments. Fields that are `null` are left out of responses.

| Field | Type | Request | Response | Description |
|---|---|---|---|---|
| `attachment_id` | integer | ignored | ✔ | Attachment ID. |
| `name` | string | create: **required**, update: optional (not empty) | ✔ | Display name. |
| `description` | string | optional | ✔ | Description. |
| `sort_order` | integer | optional (create default: `0`) | ✔ | Position on the product page, ascending. |
| `filename` | string | ignored | ✔ | Name of the stored file. If a file with the same name already exists, a suffix is added (e.g. `manual_1.pdf`). |
| `store_ids` | integer[] | optional (create default: `[0]`); must not be empty | ✔ | Store views. `0` = all store views. |
| `customer_group_ids` | integer[] | create: **required** unless `all_customer_groups` is `true`; must not be empty | ✔ | Customer groups allowed to download the file. |
| `all_customer_groups` | boolean | optional | – | If `true`, assigns every customer group that exists at the time of the request. Can't be combined with `customer_group_ids`. `false` has no effect. |
| `file_content` | [FileContent](#filecontent) | create: **required**, update: optional | – | The file itself. On update, it replaces the current file. |
| `product_skus` | string[] | ignored | ✔ | SKUs of the products the attachment is assigned to, sorted alphabetically. Manage assignments with the [product assignment](#product-assignments) endpoints. |
| `download_url` | string | ignored | ✔ | Storefront download URL. |
| `thumbnail_url` | string | ignored | ✔ | Thumbnail URL. |
| `created_at` | string | ignored | ✔ | Creation date (`Y-m-d H:i:s`). |
| `updated_at` | string | ignored | ✔ | Last update date (`Y-m-d H:i:s`). |

On update, fields that aren't sent keep their current values. For example, sending only `name` keeps the file, stores and customer groups unchanged.

### FileContent

| Field | Type | Required | Description |
|---|---|---|---|
| `file_data` | string | ✔ | Base64-encoded file content, without a `data:` prefix. |
| `name` | string | ✔ | File name with extension. It can't contain `\ / ? * : " ; < > ( ) \| { }`. |

- **Allowed files:** `jpg`, `jpeg`, `gif`, `png`, `pdf`. Both the extension and the detected MIME type are checked (`image/jpg`, `image/jpeg`, `image/gif`, `image/png`, `application/pdf`).
- **Thumbnail:** generated automatically from the image or from the first page of the PDF.

### SearchResults

| Field | Type | Description |
|---|---|---|
| `items` | [Attachment](#attachment)[] | Attachments on the requested page. |
| `search_criteria` | object | The search criteria that were applied. |
| `total_count` | integer | Number of attachments matching the filters, across all pages. |

---

## Attachments

### GET /V1/file-attachments

Returns a paginated list of attachments. It uses the standard Magento `searchCriteria` query parameters, and isn't limited by the current store or customer session.

**Query parameters**

| Parameter | Description                                                                            |
|---|----------------------------------------------------------------------------------------|
| `searchCriteria[pageSize]` | Items per page.                                                                        |
| `searchCriteria[currentPage]` | Page number, starting at 1.                                                            |
| `searchCriteria[sortOrders][0][field]` | Sort field, e.g. `sort_order`, `name`, `attachment_id`, `created_at`.                  |
| `searchCriteria[sortOrders][0][direction]` | `ASC` or `DESC`.                                                                       |
| `searchCriteria[filterGroups][N][filters][M][field]` | Filter field. See the table below.                                                     |
| `searchCriteria[filterGroups][N][filters][M][value]` | Filter value.                                                                          |
| `searchCriteria[filterGroups][N][filters][M][conditionType]` | `eq` (default), `like`, `in`, `gt`, `lt`, … Custom filters support only `eq` and `in`. |

Filters in the same filter group are combined with **OR**. Separate filter groups are combined with **AND**.

**Filter fields**

| Field | Type | Behaviour |
|---|---|---|
| `attachment_id`, `name`, `filename`, `description`, `sort_order`, `created_at`, `updated_at` | column | Standard filter, supports every `conditionType`. |
| `store_id` | custom | Attachments assigned to the given store view(s) **or** to all store views (`0`). Use `in` with a comma-separated list for several stores, e.g. `1,2`. |
| `customer_group_id` | custom | Attachments assigned to any of the given customer group(s). Use `in` with a comma-separated list for several groups. |
| `product_sku` | custom | Attachments assigned to any of the given product SKUs (exact match). Use `in` with a comma-separated list for several SKUs. |

Custom filters support the `eq` and `in` condition types. Any other type returns a 400 error.

- **`eq`:** the whole value is matched as one literal value, so a SKU containing a comma (e.g. `part,blue`) works.
- **`in`:** the value is split on commas.

Like the column filters, custom filters are combined with **OR** with the other filters in the same filter group.

**Responses**

| Status | Body | When |
|---|---|---|
| 200 | [SearchResults](#searchresults) | The list was returned. |
| 401 | Error | The token is missing or doesn't have the required permission. |

```json
{
  "items": [
    {
      "attachment_id": 12,
      "name": "Product manual",
      "description": "Installation guide",
      "sort_order": 10,
      "filename": "manual.pdf",
      "store_ids": [0],
      "customer_group_ids": [0, 1, 2, 3],
      "product_skus": ["24-MB01", "24-MB04"],
      "download_url": "https://<store-domain>/file_attachments/attachment/download/file/<hash>/id/12/",
      "thumbnail_url": "https://<store-domain>/media/file_attachments/attachment/thumbnail/<hash>.jpg",
      "created_at": "2026-09-24 10:15:00",
      "updated_at": "2026-09-24 10:15:00"
    }
  ],
  "search_criteria": { "filter_groups": [ ... ], "page_size": 20, "current_page": 1 },
  "total_count": 1
}
```

---

### POST /V1/file-attachments

Creates an attachment and uploads its file.

**Request body**

| Field | Type | Description |
|---|---|---|
| `attachment` | [Attachment](#attachment) | **Required.** The attachment data, including `file_content`. |

```json
{
  "attachment": {
    "name": "Product manual",
    "description": "Installation guide",
    "sort_order": 10,
    "store_ids": [0],
    "all_customer_groups": true,
    "file_content": {
      "file_data": "<base64-encoded file content>",
      "name": "manual.pdf"
    }
  }
}
```

**Responses**

| Status | Body | When |
|---|---|---|
| 200 | [Attachment](#attachment) | The attachment was created. |
| 400 | Error | A required field is missing, the base64 or file name is invalid, the file type isn't allowed, an empty list was sent, or `customer_group_ids` and `all_customer_groups: true` were both sent. |
| 401 | Error | The token is missing or doesn't have the required permission. |

---

### PUT /V1/file-attachments/{attachmentId}

Updates an attachment. Only the fields that are sent are changed. If `file_content` is sent, the file is replaced, and the previous file and its thumbnail are deleted.

**Path parameters**

| Parameter | Type | Description |
|---|---|---|
| `attachmentId` | integer | **Required.** Attachment ID. |

**Request body**

| Field | Type | Description |
|---|---|---|
| `attachment` | [Attachment](#attachment) | **Required.** The fields to change. |

```json
{
  "attachment": {
    "name": "Product manual (v2)",
    "customer_group_ids": [1, 2]
  }
}
```

**Responses**

| Status | Body | When |
|---|---|---|
| 200 | [Attachment](#attachment) | The attachment was updated. |
| 400 | Error | Validation failed. Same rules as create, except that no field is required. |
| 401 | Error | The token is missing or doesn't have the required permission. |
| 404 | Error | The attachment doesn't exist. |

---

### DELETE /V1/file-attachments/{attachmentId}

Deletes an attachment together with its file, its thumbnail, and all its store, customer group and product assignments.

**Path parameters**

| Parameter | Type | Description |
|---|---|---|
| `attachmentId` | integer | **Required.** Attachment ID. |

**Responses**

| Status | Body | When |
|---|---|---|
| 200 | `true` | The attachment was deleted. |
| 401 | Error | The token is missing or doesn't have the required permission. |
| 404 | Error | The attachment doesn't exist. |

---

## Product assignments

These endpoints manage which attachments are shown on a product page.

### GET /V1/products/{sku}/file-attachments

Returns the attachments assigned to a product, sorted by `sort_order`.

**Path parameters**

| Parameter | Type | Description |
|---|---|---|
| `sku` | string | **Required.** Product SKU, URL-encoded. |

**Responses**

| Status | Body | When |
|---|---|---|
| 200 | [Attachment](#attachment)[] | Returns `[]` when the product has no attachments. |
| 401 | Error | The token is missing or doesn't have the required permission. |
| 404 | Error | The product doesn't exist. |

---

### POST /V1/products/{sku}/file-attachments

Assigns attachments to a product. Existing assignments are kept, and IDs that are already assigned are ignored.

**Path parameters**

| Parameter | Type | Description |
|---|---|---|
| `sku` | string | **Required.** Product SKU, URL-encoded. |

**Request body**

| Field | Type | Description |
|---|---|---|
| `attachmentIds` | integer[] | **Required.** IDs of the attachments to assign. |

```json
{
  "attachmentIds": [1, 2]
}
```

**Responses**

| Status | Body | When |
|---|---|---|
| 200 | [Attachment](#attachment)[] | All attachments assigned to the product after the change. |
| 401 | Error | The token is missing or doesn't have the required permission. |
| 404 | Error | The product or one of the attachments doesn't exist. The message lists the missing IDs, and no assignments are changed. |

---

### POST /V1/products/{sku}/file-attachments/replace

Replaces all of the product's assignments with the given list. Send an empty list to remove all assignments.

This is a `POST` rather than a `PUT` because of a Magento web API limitation. For `PUT` requests with a path parameter and a single array in the body, Magento tries to treat the array as a data object and fails with `Class "int[]" does not exist`.

**Path parameters**

| Parameter | Type | Description |
|---|---|---|
| `sku` | string | **Required.** Product SKU, URL-encoded. |

**Request body**

| Field | Type | Description |
|---|---|---|
| `attachmentIds` | integer[] | **Required.** The complete list of attachment IDs for the product. `[]` removes all. |

```json
{
  "attachmentIds": [3, 5]
}
```

**Responses**

| Status | Body | When |
|---|---|---|
| 200 | [Attachment](#attachment)[] | All attachments assigned to the product after the change. |
| 401 | Error | The token is missing or doesn't have the required permission. |
| 404 | Error | The product or one of the attachments doesn't exist. No assignments are changed. |

---

### DELETE /V1/products/{sku}/file-attachments/{attachmentId}

Removes one attachment from a product. The attachment itself isn't deleted.

**Path parameters**

| Parameter | Type | Description |
|---|---|---|
| `sku` | string | **Required.** Product SKU, URL-encoded. |
| `attachmentId` | integer | **Required.** ID of the attachment to remove from the product. |

**Responses**

| Status | Body | When |
|---|---|---|
| 200 | `true` | The assignment was removed. |
| 401 | Error | The token is missing or doesn't have the required permission. |
| 404 | Error | The product doesn't exist, or the attachment isn't assigned to it. |
