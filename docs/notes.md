│ Summary                                                              │
│                                                                      │
│ Creating a new sub-client currently fails because we try to persist a│
│  sub-site tied to the main client’s head site. The database trigger  │
│ fn_sites_same_client rejects this insert since the child site uses   │
│ the new sub-client’s client_id, which does not match the parent      │
│ site’s client_id. We will make the backend gracefully fall back to   │
│ storing the sub-client’s location as a normal head site while still  │
│ allowing validation to confirm the selected parent site belongs to   │
│ the chosen main client.                                              │
│                                                                      │
│ Planned Changes                                                      │
│ 1. `SitesModel::saveSubSite`                                         │
│   •  Add an optional $options argument (e.g. ['expected_client_id'   │
│      => …, 'fallback_to_head_site' => true]).                        │
│   •  When the parent site belongs to a different client but matches  │
│      expected_client_id, skip the strict same-client requirement     │
│      and, instead of failing, delegate to saveHeadSite() (clearing   │
│      parent_site_id) so the sub-client still receives a valid        │
│      head-site record.                                               │
│   •  Ensure cache refresh logic still runs in both branches.         │
│                                                                      │
│ 2. `ClientsController::handleFormSubmission`                         │
│   •  Pass the main client id context into saveSubSite() via the new  │
│      options payload.                                                │
│   •  Recognize when saveSubSite() fell back to a head-site save and  │
│      avoid treating it as an error.                                  │
│   •  Preserve existing validation flow (which already supplies       │
│      expected_client_id) so the UX still surfaces invalid parent     │
│      selections.                                                     │
│                                                                      │
│ 3. Safety & Compatibility                                            │
│   •  No schema changes are required; the fallback keeps writes       │
│      compatible with the current trigger while still storing the     │
│      sub-client’s location data.                                     │
│   •  Ensure existing same-client sub-site saves continue behaving    │
│      exactly as before.                                              │
│                                                                      │
│ Verification                                                         │
│ •  Create a new main client → confirm success.                       │
│ •  Create a new sub-client linked to an existing main client →       │
│    confirm it saves without the previous error and that its location │
│    data persists.                                                    │
│ •  Update an existing sub-client’s location to verify the fallback   │
│    path also handles updates.                                        │
│ •  Quick regression check: create/update a main client with a        │
│    standard head site to ensure we haven’t affected the default      │
│    path.  