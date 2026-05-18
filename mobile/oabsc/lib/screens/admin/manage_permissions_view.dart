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

  final _codeController = TextEditingController();
  final _descController = TextEditingController();
  bool _addingPermission = false;

  @override
  void initState() {
    super.initState();
    _fetch();
  }

  @override
  void dispose() {
    _codeController.dispose();
    _descController.dispose();
    super.dispose();
  }

  String _formatRoleName(String name) {
    if (name.isEmpty) return '';
    return name.replaceAll('_', ' ').toUpperCase();
  }

  Future<void> _addPermission() async {
    final code = _codeController.text.trim();
    final desc = _descController.text.trim();
    if (code.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Permission code is required.')),
      );
      return;
    }

    setState(() => _addingPermission = true);
    try {
      final r = await _api.post('admin/permissions/add', {
        'code': code,
        'description': desc,
      });

      if (mounted) {
        if (r['success'] == true) {
          _codeController.clear();
          _descController.clear();
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(r['message'] ?? 'Permission added successfully.')),
          );
          _fetch();
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(r['message'] ?? 'Failed to add permission.')),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('An error occurred: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _addingPermission = false);
    }
  }

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
                    child: Wrap(
                      crossAxisAlignment: WrapCrossAlignment.center,
                      spacing: 6,
                      runSpacing: 4,
                      children: const [
                        Icon(Icons.grid_on_rounded, size: 14, color: _navy),
                        Text('Role-Permission Matrix', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: _navy)),
                        Text('Toggle to enable/disable. Changes save automatically.', style: TextStyle(fontSize: 11, color: _slate)),
                      ],
                    ),
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
                      headingRowColor: WidgetStateProperty.all(_panelHdr),
                      dividerThickness: 0.8,
                      columns: [
                        const DataColumn(label: Text('PERMISSION', style: _thStyle)),
                        ..._roles.map((r) {
                          final count = _roleCounts[r['name']] ?? 0;
                          return DataColumn(
                            label: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Text(_formatRoleName(r['name'] ?? ''), style: _thStyle),
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
                            final isAdmin = r['name'] == 'admin';
                            return DataCell(
                              Center(
                                child: Transform.scale(
                                  scale: 0.7,
                                  child: CupertinoSwitch(
                                    value: isAdmin ? true : hasPerm,
                                    activeTrackColor: _activeTg,
                                    onChanged: isAdmin ? null : (v) => _toggle(rId, pId, hasPerm),
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

              const SizedBox(height: 20),

              // ── Add New Permission ──────────────────────────────
              _Panel(
                header: Row(children: const [
                  Icon(Icons.add_circle_outline_rounded, size: 14, color: _navy),
                  SizedBox(width: 6),
                  Text('Add New Permission', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: _navy)),
                ]),
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 12),
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    const Text('CODE', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: _slate, letterSpacing: 0.5)),
                    const SizedBox(height: 6),
                    TextField(
                      controller: _codeController,
                      decoration: InputDecoration(
                        hintText: 'e.g. view_reports',
                        hintStyle: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: _border)),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: _border)),
                        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFF2A6A7E))),
                        fillColor: const Color(0xFFFAFAFA),
                        filled: true,
                      ),
                      style: const TextStyle(fontSize: 13),
                    ),
                    const SizedBox(height: 12),
                    const Text('DESCRIPTION', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: _slate, letterSpacing: 0.5)),
                    const SizedBox(height: 6),
                    TextField(
                      controller: _descController,
                      decoration: InputDecoration(
                        hintText: 'Short label',
                        hintStyle: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: _border)),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: _border)),
                        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFF2A6A7E))),
                        fillColor: const Color(0xFFFAFAFA),
                        filled: true,
                      ),
                      style: const TextStyle(fontSize: 13),
                    ),
                    const SizedBox(height: 16),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: _addingPermission ? null : _addPermission,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF2A6A7E),
                          foregroundColor: Colors.white,
                          elevation: 0,
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                        child: _addingPermission
                          ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                          : const Text('Add Permission', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
                      ),
                    ),
                  ]),
                ),
              ),
            ],
          ]),
        ),
      ),
    );
  }
}

class _Panel extends StatelessWidget {
  final Widget header;
  final Widget child;
  const _Panel({required this.header, required this.child});

  @override
  Widget build(BuildContext context) => Container(
    decoration: BoxDecoration(color: _white, borderRadius: BorderRadius.circular(10), border: Border.all(color: _border),
      boxShadow: const [BoxShadow(color: Color(0x050F172A), blurRadius: 4, offset: Offset(0, 1))]),
    clipBehavior: Clip.hardEdge,
    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Container(width: double.infinity, padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16), color: _panelHdr, child: header),
      const Divider(height: 1, color: Color(0xFFF1F5F9)),
      Padding(padding: const EdgeInsets.all(8), child: child),
    ]),
  );
}

const _thStyle = TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: Color(0xFF64748B), letterSpacing: 0.5);
