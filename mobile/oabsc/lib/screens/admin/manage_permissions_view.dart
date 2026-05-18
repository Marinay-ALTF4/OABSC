import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';
import '../../services/api_service.dart';
const _bg       = Color(0xFFEDF2F7);
const _white    = Colors.white;
const _navy     = Color(0xFF0F172A);
const _slate    = Color(0xFF64748B);
const _slate3   = Color(0xFF334155);
const _border   = Color(0xFFE2E8F0);
const _panelHdr = Color(0xFFF8FAFC);
const _activeTg = Color(0xFF10B981); // Green matching screenshot

class ManagePermissionsView extends StatefulWidget {
  const ManagePermissionsView({super.key});
  @override
  State<ManagePermissionsView> createState() => _ManagePermissionsViewState();
}

class _ManagePermissionsViewState extends State<ManagePermissionsView> {
  final _api = ApiService();
  bool _loading = true;
  List _roles = [];
  List _permissions = [];
  final Map<int, List<int>> _mapping = {};
  Map<String, int> _roleCounts = {};

  @override
  void initState() { super.initState(); _fetch(); }

  Future<void> _fetch() async {
    setState(() => _loading = true);
    try {
      final r = await _api.get('admin/permissions');
      if (mounted && r['success'] == true) {
        setState(() {
          _roles = List.from(r['roles'] ?? []);
          _permissions = List.from(r['permissions'] ?? []);
          _roleCounts = Map<String, int>.from(r['roleCounts'] ?? {});
          _mapping.clear();
          final m = r['mapping'] as Map?;
          if (m != null) {
            m.forEach((k, v) {
              _mapping[int.parse(k)] = List<int>.from(v);
            });
          }
        });
      }
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  bool _hasPermission(int roleId, int permId) {
    return _mapping[roleId]?.contains(permId) ?? false;
  }

  Future<void> _toggle(int roleId, int permId, bool current) async {
    // Optimistic update
    setState(() {
      if (!_mapping.containsKey(roleId)) _mapping[roleId] = [];
      if (current) {
        _mapping[roleId]!.remove(permId);
      } else {
        _mapping[roleId]!.add(permId);
      }
    });

    try {
      final r = await _api.post('admin/permissions/toggle', {
        'role_id': roleId,
        'permission_id': permId,
        'action': current ? 'revoke' : 'assign'
      });
      if (r['success'] != true) {
        _revert(roleId, permId, current);
      }
    } catch (_) {
      _revert(roleId, permId, current);
    }
  }

  void _revert(int roleId, int permId, bool original) {
    if (mounted) {
      setState(() {
        if (original) {
          _mapping[roleId]!.add(permId);
        } else {
          _mapping[roleId]!.remove(permId);
        }
      });
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Failed to update permission')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      color: _bg,
      child: RefreshIndicator(
        onRefresh: _fetch,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            // ── Header ────────────────────────────────────────
            Row(children: const [
              Icon(Icons.shield_outlined, size: 20, color: _navy),
              SizedBox(width: 8),
              Text('Manage Permissions', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: _navy)),
            ]),
            const SizedBox(height: 2),
            const Text('Toggle permissions per role. Changes apply immediately.', style: TextStyle(fontSize: 12, color: _slate)),
            const SizedBox(height: 16),

            if (_loading) const Center(child: Padding(padding: EdgeInsets.all(40), child: CircularProgressIndicator()))
            else ...[
              // ── Matrix Table ──────────────────────────────────
              Container(
                decoration: BoxDecoration(color: _white, borderRadius: BorderRadius.circular(10), border: Border.all(color: _border),
                  boxShadow: const [BoxShadow(color: Color(0x050F172A), blurRadius: 4, offset: Offset(0, 1))]),
                clipBehavior: Clip.hardEdge,
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Container(
                    width: double.infinity, padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16), color: _panelHdr,
                    child: Row(children: const [
                      Icon(Icons.grid_on_rounded, size: 14, color: _navy),
                      SizedBox(width: 6),
                      Text('Role-Permission Matrix', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: _navy)),
                      SizedBox(width: 6),
                      Text('Toggle to enable/disable. Changes save automatically.', style: TextStyle(fontSize: 11, color: _slate)),
                    ]),
                  ),
                  const Divider(height: 1, color: Color(0xFFF1F5F9)),
                  
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: DataTable(
                      headingRowHeight: 48,
                      dataRowMinHeight: 48,
                      dataRowMaxHeight: 56,
                      horizontalMargin: 16,
                      columnSpacing: 24,
                      headingRowColor: WidgetStateProperty.all(_white),
                      dividerThickness: 0.8,
                      columns: [
                        const DataColumn(label: Text('PERMISSION', style: _thStyle)),
                        ..._roles.map((r) {
                          final count = _roleCounts[r['name']] ?? 0;
                          return DataColumn(
                            label: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Text((r['name'] ?? '').toString().toUpperCase(), style: _thStyle),
                                Text('$count USER(S)', style: const TextStyle(fontSize: 8, color: _slate, fontWeight: FontWeight.w600)),
                              ]
                            )
                          );
                        }),
                      ],
                      rows: _permissions.map((p) {
                        final pId = int.parse(p['id'].toString());
                        final code = p['code'].toString();
                        final desc = p['description']?.toString() ?? '';
                        return DataRow(cells: [
                          DataCell(
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Text(code, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: _slate3)),
                                if (desc.isNotEmpty) Text(desc, style: const TextStyle(fontSize: 10, color: _slate)),
                              ]
                            )
                          ),
                          ..._roles.map((r) {
                            final rId = int.parse(r['id'].toString());
                            final hasPerm = _hasPermission(rId, pId);
                            return DataCell(
                              Center(
                                child: Transform.scale(
                                  scale: 0.7,
                                  child: CupertinoSwitch(
                                    value: hasPerm,
                                    activeTrackColor: _activeTg,
                                    onChanged: (v) => _toggle(rId, pId, hasPerm),
                                  ),
                                ),
                              ),
                            );
                          }),
                        ]);
                      }).toList(),
                    ),
                  ),
                ]),
              ),
            ],
          ]),
        ),
      ),
    );
  }
}

const _thStyle = TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: Color(0xFF1E293B), letterSpacing: 0.5);
