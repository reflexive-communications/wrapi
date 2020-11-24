# WrAPI

## Summary

This extension adds a new REST API endpoint, which is basically a wrapper for the CiviCRM stock REST endpoint
(hence the name: API wrapper = WrAPI :). It allows custom handlers to listen to this endpoint, and take any action to handle the request.
In the stock API you can only do atomic operations, like add contact, add new tag, add tag to contact, etc. If you need more complex workflow
to handle an event from outside of CiviCRM, you have to call more REST HTTP request.

For example:
- You want to handle a situation, like registration on a different site.
- Your desired workflow is:
    1. Check email address
    1. If email not present --> Create new contact
    1. If email present --> Don't create
    1. Add a tag to this contact (based on the request)

With the stock API, you can achieve this with:
- Email::get
- Contact::create or Contact::get
- EntityTag::create

This means three HTTP requests, and the logic (check if email is already registered) have to be done on the source site, so effectively CiviCRM is passive in this arrangement.

With this new endpoint it is possible to combine this request to one request for CiviCRM to handle, calling the same API calls, performing the same logic.
So CiviCRM will handle requests more actively, deciding on what to do with the received request and reducing request round-trips.

### Note

There is an example handler supplied (and some handlers for debugging), but to handle ***your*** requests, some **development effort** (coding) is required.

Basically it is relatively easy (at least I hope).
Authentication, routing and route administration is already implemented, so what you only need to do is to write the custom logic and API calls.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.2+
* CiviCRM (5.24 might work below - not tested)

## Installation

Learn more about installing CiviCRM extensions in the [CiviCRM Sysadmin Guide](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/).

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://gitlab.com/semsey_sandor_civicrm/extensions/wrapi.git
cv en wrapi
```

## Getting Started

In the **Administer** >> **WrAPI** menu you can manage your routes, and update setting for WrAPI.

Routing is based on `action` parameter received in the request. Here you can assign Handlers (CRM_Wrapi_Handler_* classes) to the actions.

For further reference check [Developer Notes](dev_notes.md).

***Contributors welcome! :)***
